<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\OrderResource;
use App\Jobs\ProcessPoints;
use App\Mail\OrderPlacedForCustomer;
use App\Mail\OrderPlacedForTeam;
use App\Mail\OrderReadyForTakeaway;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Codedge\Fpdf\Fpdf\Fpdf;
use Exception;
use Carbon\Carbon;

class OrderController extends Controller
{


    public function generatePDF($id)
    {
        $order = Order::with('orderItems')->find($id);
        if ($order === null) {
            $pdf = new Fpdf;
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Cell(195, 30, "Es wurde keine Bestellung mit der Nummer $id gefunden.", 0, 1, 'C');
            $pdf->Image(storage_path('app/rk_logo.png'), 72.5, 250, 70);
    
            $pdfOutput = $pdf->Output('S'); // 'S' parameter to return PDF as a string
    
            // Return a response with PDF headers
            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Intern_RKHL_Bestellung-' . $id . '.pdf"');
        }

        $employee = Employee::where('remoteId', $order->remoteId)->firstOrFail();
        
        $pdf = new Fpdf();
        $pdf->AddPage();

        /* HEADER */
        $pdf->SetFillColor(130,40,40);
        $pdf->Rect(0, 0, $pdf->GetPageWidth(), 7, 'F');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(255,255,255);
        $pdf->Text(10, 5, 'intern.rkhl.at');

        /* ORDER NUMBER */
        $pdf->SetFont('Helvetica', 'B', 15);
        $pdf->SetXY(10,10);
        $pdf->SetTextColor(20,20,20);
        $pdf->SetDrawColor(255,100,100);
        $pdf->Cell(190,13,mb_convert_encoding('Bestellung Nr. ' . $order->id, 'ISO-8859-1', 'UTF-8'),0,1,'C');
        $pdf->SetFillColor(130,40,40);
        $pdf->Rect(70, 21, 70, 0.4, 'F');


        /* LEFT PART */

        $pdf->SetXY(45,35);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(39,0,mb_convert_encoding('Bestellt von' , 'ISO-8859-1', 'UTF-8'),0,0,'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(40,0,mb_convert_encoding($employee->firstname . ' ' . $employee->lastname, 'ISO-8859-1', 'UTF-8'),0,0,'L');

        $pdf->SetXY(45,40);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(39,0,mb_convert_encoding('Telefonnr.' , 'ISO-8859-1', 'UTF-8'),0,0,'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(40,0,mb_convert_encoding($employee->phone, 'ISO-8859-1', 'UTF-8'),0,0,'L');

        $pdf->SetXY(45,45);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(39,0,mb_convert_encoding('Email' , 'ISO-8859-1', 'UTF-8'),0,0,'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(40,0,mb_convert_encoding($employee->email, 'ISO-8859-1', 'UTF-8'),0,0,'L');

        $pdf->SetXY(45,55);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(39,0,mb_convert_encoding('Bestelldatum' , 'ISO-8859-1', 'UTF-8'),0,0,'L');
        $pdf->SetFont('Helvetica', '', 10);
        $viennaTime = Carbon::parse($order->created_at_datetime)->timezone('Europe/Vienna')->format('d.m.Y H:i:s');
        $pdf->Cell(40,0,mb_convert_encoding($viennaTime, 'ISO-8859-1', 'UTF-8'),0,0,'L');

        $pdf->SetXY(45,60);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(39,0,mb_convert_encoding('Ausgegebene Pkt.' , 'ISO-8859-1', 'UTF-8'),0,0,'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(40,0,mb_convert_encoding(number_format($order->total_points,0,',','.'), 'ISO-8859-1', 'UTF-8'),0,0,'L');


        

        $pdf->SetXY(145,39);
        $pdf->SetDrawColor(100,100,100);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50,4,mb_convert_encoding('Ausgegeben am' , 'ISO-8859-1', 'UTF-8'),"T",0,'C');

        $pdf->SetXY(145,58);
        $pdf->SetDrawColor(100,100,100);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(50,4,mb_convert_encoding('Ausgegeben von' , 'ISO-8859-1', 'UTF-8'),"T",0,'C');



        $imageBase64 = $employee->picture_base64;
        if(strlen($imageBase64) < 100){
            $pdf->SetTextColor(150,150,150);
            $pdf->SetDrawColor(100,100,100);
            $pdf->Rect(15, 32.5, 25, 30, 0);
            $pdf->Text(20, 47.5, 'Kein Foto');
        }else{
            $parts = explode(',', $imageBase64, 2);
            $dataType = $parts[0]; // Contains data type and encoding information
            $base64 = $parts[1];
            $imageData = base64_decode($base64);
            
            if (getimagesizefromstring($imageData) === false) {
                die('Decoded data is not a valid image.');
            }
            
            // Extract MIME type
            preg_match('/^data:image\/(\w+);/', $dataType, $matches);
            if (!$matches) {
                die('Could not determine the image type.');
            }
            
            // Determine the correct file extension based on MIME type
            $imageType = strtolower($matches[1]);
            if ($imageType === 'jpeg') {
                $imageType = 'jpg'; // Normalize jpeg to jpg
            }
            $imagePath = storage_path('app/public/temp_image.' . $imageType);
            
            // Save image file with the correct extension
            file_put_contents($imagePath, $imageData);
            if (!file_exists($imagePath) || !is_readable($imagePath)) {
                die("The file does not exist or is not readable: $imagePath");
            }
            $pdf->Image($imagePath, 15, 30, 25);
            unlink($imagePath);
        }



        $pdf->SetFillColor(130,40,40);
        $pdf->Rect(10, 70, 190, 0.4, 'F');




        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(10,75);
        $pdf->SetTextColor(20,20,20);
        $pdf->Cell(190,13,mb_convert_encoding('Bestellte Produkte', 'ISO-8859-1', 'UTF-8'),0,1,'C');


        
        $pdf->SetXY(10,87);
        $height = 19;
        $aspectRatio = 300 / 245;
        $width = $height * $aspectRatio;


        $pdf->SetDrawColor(150,150,150);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(24.25, 7, "Produktbild", 1);
        $pdf->Cell(60, 7, "Produktname", 1);
        $pdf->Cell(19, 7, "Artikel Nr.", 1, '', 'C');
        $pdf->Cell(13, 7, "Menge", 1, '', 'C');
        $pdf->Cell(15, 7, "Punkte", 1, '', 'C');
        $pdf->Cell(59, 7, "Notiz", 1, '', 'L');
        $pdf->Ln();  // Move to the next line


        $orderItems = $order->orderItems->sortByDesc('points');
        

        foreach ($orderItems as $item) {
            $pdf->SetDrawColor(150,150,150);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Cell(24.25, 20, "", 1);
            $imagePath = storage_path('app/public/' . $item->src1);
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, $x + 0.5, $y + 0.5, $width, $height);
            }


            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Cell(60, 20, "" , 1);
            $pdf->SetTextColor(20,20,20);
            $pdf->SetFont('Helvetica', 'B', 9);
            if($pdf->GetStringWidth($item->name) > 55){
                $item->name = substr($item->name, 0, 30) . '...';
            }
            $pdf->Text($x+1,$y+5,mb_convert_encoding($item->name, 'ISO-8859-1', 'UTF-8'));
            $pdf->SetTextColor(111,111,111);
            $pdf->SetFont('Helvetica', '', 7);
            // if slogan is longer than 55mm then find the next space after 35 characters. print the first part and then continue with the rest of the slogan. maximum are 3 lines 
            //$pdf->Text($x+1,$y+10,mb_convert_encoding($item->slogan, 'ISO-8859-1', 'UTF-8'));

            $encodedSlogan = mb_convert_encoding($item->slogan, 'ISO-8859-1', 'UTF-8');
            $SloganWidth = $pdf->GetStringWidth($encodedSlogan);

            // Check if the width is greater than 55mm
            $x = $x + 1;
            $y = $y + 10;
            if ($SloganWidth > 55) {
                $this->splitStringToLines($pdf, $encodedSlogan, $x, $y, 55,3);
            } else {
                $pdf->Text($x, $y, $encodedSlogan);
            }


            $pdf->SetTextColor(20,20,20);
            $pdf->Cell(19, 20, $item->article_number, 1, '', 'C');
            $pdf->Cell(13, 20, $item->quantity, 1, '', 'C');
            $pdf->Cell(15, 20, number_format($item->points,0,',','.'), 1, '', 'C');


            $encodedNote = mb_convert_encoding($item->note, 'ISO-8859-1', 'UTF-8');
            $noteWidth = $pdf->GetStringWidth($encodedNote);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $x = $x + 1;
            $y = $y + 3;
            $pdf->SetTextColor(111,111,111);
            $pdf->SetFont('Helvetica', '', 7);
            $pdf->Cell(59, 20, "", 1);
            if ($noteWidth > 58) {
                $this->splitStringToLines($pdf, $encodedNote, $x, $y, 58,4);
            } else {
                $pdf->Text($x, $y, $encodedNote);
            }
            $pdf->Ln();  // Move to the next line
        }

        
        $pdf->Image(storage_path('app/rk_logo.png'), 72.5, 250, 70);

        $pdfOutput = $pdf->Output('S'); // 'S' parameter to return PDF as a string

        // Return a response with PDF headers
        return response($pdfOutput, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Intern_RKHL_Bestellung-' . $id . '.pdf"');
    }
    private function splitStringToLines($pdf, $string, $x, $y, $maxWidth, $maxLines)
    {
        $words = explode(' ', $string);
        $line = '';
        $lineCount = 0;

        foreach ($words as $word) {
            $testLine = $line . ($line ? ' ' : '') . $word;
            $testWidth = $pdf->GetStringWidth($testLine);

            if ($testWidth > $maxWidth && $line !== '') {
                $pdf->Text($x, $y + ($lineCount * 3.5), $line);
                $line = $word;
                $lineCount++;
                if ($lineCount >= 3) break;
            } else {
                $line = $testLine;
            }
        }
        if ($line !== '' && $lineCount < $maxLines) {
            $pdf->Text($x, $y + ($lineCount * 3.5), $line);
        }
    }

    //
    public function getSelfOrders(Request $request)
    {
        $user = $request->user();
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::where('remoteId', $user->remoteId)->with('orderItems')->orderBy('created_at', 'desc')->paginate(250);

        return OrderResource::collection($orders);
    }


    public function getOrders(Request $request)
    {
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::with('orderItems')->orderBy('created_at', 'asc')->paginate(5000);

        return OrderResource::collection($orders);
    }

    
    public function employeesFromOrders(Request $request){
        $orders = Order::get();

        $employeeIds = [];
        foreach ($orders as $order) {
            $employeeIds[$order->remoteId] = true;

            $employeeIds[$order->remoteId] = true;
            if($order->state_1_user_id != null){
                $employeeIds[$order->state_1_user_id] = true;
            }
            if($order->state_2_user_id != null){
                $employeeIds[$order->state_2_user_id] = true;
            }
            if($order->state_3_user_id != null){
                $employeeIds[$order->state_3_user_id] = true;
            }
            if($order->state_4_user_id != null){
                $employeeIds[$order->state_4_user_id] = true;
            }
            if($order->state_5_user_id != null){
                $employeeIds[$order->state_5_user_id] = true;
            }
        }
        $employeeIds = array_keys($employeeIds);

        $employees = Employee::whereIn('remoteId', $employeeIds)->get();
        return EmployeeResource::collection($employees);
    }

    
    public function changeOrderState(Request $request, $id)
    {
        $user = $request->user();
        $newState = $request->input('state');

        $order = Order::findOrFail($id);
        if (!in_array($newState, [0, 1, 2, 3, 4, 5])) {
            return response()->json(['message' => 'Invalid state.'], 400);
        }

        if ($newState == 0) {
            //Offen
            $order->update([
                'state' => $newState,
                'state_1_datetime' => null, 'state_1_user_id' => null,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 1) {
            //In Prüfung (Nicht in Verwendung)
            $order->update([
                'state' => $newState,
                'state_1_datetime' => now(), 'state_1_user_id' => $user->remoteId,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 2) {
            //Ist Bestellt
            $order->update([
                'state' => $newState,
                'state_2_datetime' => now(), 'state_2_user_id' => $user->remoteId,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 3) {
            //Abholbereit
            $order->update([
                'state' => $newState,
                'state_3_datetime' => now(), 'state_3_user_id' => $user->remoteId,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);

            $employee = Employee::where('remoteId',$order->remoteId)->first();
            if ($employee) {
                Mail::to($employee->email)->send(new OrderReadyForTakeaway($order,$employee));
            }
        } else if ($newState == 4) {
            //Erledigt (Nicht in Verwendung)
            $order->update([
                'state' => $newState,
                'state_4_datetime' => now(), 'state_4_user_id' => $user->remoteId,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 5) {
            $order->update([
                'state' => $newState,
                'state_1_datetime' => null, 'state_1_user_id' => null,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => now(), 'state_5_user_id' => $user->remoteId,
            ]);   
        }
        $employee = Employee::where('remoteId',$order->remoteId)->first();
        if ($employee) {
            ProcessPoints::dispatch($employee);
        }

        return response()->json(['message' => 'Order state updated successfully.'], 200);
    }


    public function mailConfirmationAgain(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $employee = Employee::where('remoteId', $order->remoteId)->firstOrFail();
        Mail::to($employee->email)->send(new OrderPlacedForCustomer($order));

        $employees = Employee::where('isModerator', true)->orWhere('isAdministrator', true)->orWhere('isDeveloper', true)->get(['email']);
        $teamEmails = array();
        foreach ($employees as $employeeMail) {
            $teamEmails[] = $employeeMail->email;
        }
        foreach ($teamEmails as $teamEmail) {
            Mail::to($teamEmail)->send(new OrderPlacedForTeam($order));
        }
    }


    public function updateOrderNote(Request $request, $orderId)
    {    
        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        $note = $request->input('note', '');
        $order->note = $note;
        $order->save();
    
        return response()->json(['message' => 'Order note updated successfully']);
    }


}
