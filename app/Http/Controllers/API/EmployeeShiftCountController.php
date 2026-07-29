<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\EmployeeShiftCategories;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeShiftCountController extends Controller
{
    private const LOCATIONS = [38, 39, 82, 3316];
    private const TIMEZONE = 'Europe/Vienna';

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4', 'between:2000,2100'],
        ]);

        $year = (int) $validated['year'];
        $yearStart = CarbonImmutable::create($year, 1, 1, 0, 0, 0, self::TIMEZONE);
        $yearEnd = $yearStart->addYear();

        $counts = DB::table('shifts')
            ->join('employees', 'employees.remoteId', '=', 'shifts.employeeId')
            ->whereIn('shifts.location', self::LOCATIONS)
            ->where('shifts.start', '>=', $yearStart->toDateTimeString())
            ->where('shifts.start', '<', $yearEnd->toDateTimeString())
            ->where('shifts.demandType', 'not like', 'KFZ%')
            ->groupBy('employees.remoteId')
            ->orderBy('employees.remoteId')
            ->select('employees.remoteId as employeeId')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'SUM(CASE WHEN shifts.demandType IN ('.$this->placeholders(EmployeeShiftCategories::RTW).') THEN 1 ELSE 0 END) as rtw',
                EmployeeShiftCategories::RTW
            )
            ->selectRaw(
                'SUM(CASE WHEN shifts.demandType IN ('.$this->placeholders(EmployeeShiftCategories::NEF).') THEN 1 ELSE 0 END) as nef',
                EmployeeShiftCategories::NEF
            )
            ->selectRaw(
                'SUM(CASE WHEN shifts.demandType IN ('.$this->placeholders(EmployeeShiftCategories::BKTW).') THEN 1 ELSE 0 END) as bktw',
                EmployeeShiftCategories::BKTW
            )
            ->selectRaw(
                'SUM(CASE WHEN shifts.demandType IN ('.$this->placeholders(EmployeeShiftCategories::KTW).') THEN 1 ELSE 0 END) as ktw',
                EmployeeShiftCategories::KTW
            )
            ->get()
            ->map(function ($row) {
                $bktw = (int) $row->bktw;
                $ktw = (int) $row->ktw;

                return [
                    'employeeId' => (int) $row->employeeId,
                    'total' => (int) $row->total,
                    'rtw' => (int) $row->rtw,
                    'nef' => (int) $row->nef,
                    'bktw' => $bktw,
                    'ktw' => $ktw,
                    // Deprecated: kept temporarily for backwards compatibility.
                    'bktwKtw' => $bktw + $ktw,
                ];
            });

        return response()->json(['data' => $counts]);
    }

    private function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }
}
