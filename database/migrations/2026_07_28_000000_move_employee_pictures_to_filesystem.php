<?php

use App\Services\EmployeePictureStorage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveEmployeePicturesToFilesystem extends Migration
{
    public function up()
    {
        $pictureStorage = app(EmployeePictureStorage::class);

        DB::table('employees')
            ->select(['id', 'remoteId', 'picture_base64'])
            ->whereNotNull('picture_base64')
            ->where('picture_base64', '<>', '')
            ->orderBy('id')
            ->chunkById(50, function ($employees) use ($pictureStorage) {
                foreach ($employees as $employee) {
                    if (!$pictureStorage->putBase64($employee->remoteId, $employee->picture_base64)) {
                        echo "Warnung: Ungültiges Bild bei Mitarbeiter {$employee->remoteId} — übersprungen.\n";
                    }
                }
            });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE employees DROP COLUMN picture_base64');
        } else {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('picture_base64');
            });
        }
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->longText('picture_base64')->nullable()->after('showNameInRanking');
        });

        $pictureStorage = app(EmployeePictureStorage::class);
        DB::table('employees')
            ->select(['id', 'remoteId'])
            ->orderBy('id')
            ->chunkById(50, function ($employees) use ($pictureStorage) {
                foreach ($employees as $employee) {
                    $picture = $pictureStorage->getBase64($employee->remoteId);
                    if ($picture !== null) {
                        DB::table('employees')
                            ->where('id', $employee->id)
                            ->update(['picture_base64' => $picture]);
                    }
                }
            });
    }
}
