<?php

namespace App\Services;

use App\Models\FutureShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeShiftStatus
{
    // Resolve a whole employee list together, without one query per employee.
    public function forEmployees(array $employeeIds, ?Carbon $now = null): array
    {
        if (empty($employeeIds)) {
            return [];
        }
        $timestamp = ($now ?? Carbon::now('Europe/Vienna'))->copy()
            ->setTimezone('Europe/Vienna')->format('Y-m-d H:i:s');
        $statuses = array_fill_keys($employeeIds, [
            'last_shift_date' => null,
            'next_shift_date' => null,
            'current_shift' => null,
            'next_shift' => null,
        ]);

        $completed = Shift::query()->whereIn('employeeId', $employeeIds)
            ->where('end', '<=', $timestamp)
            ->select('employeeId', DB::raw('MAX(start) as last_shift'))->groupBy('employeeId')->get();
        foreach ($completed as $shift) {
            $statuses[$shift->employeeId]['last_shift_date'] = $shift->last_shift;
        }

        // Imported plans bridge the gap until the nightly historical import.
        $planned = FutureShift::query()->whereIn('employee_id', $employeeIds)
            ->get(['employee_id', 'begin', 'end']);
        $running = Shift::query()->whereIn('employeeId', $employeeIds)
            ->where('start', '<=', $timestamp)->where('end', '>', $timestamp)
            ->get(['employeeId as employee_id', 'start as begin', 'end']);
        foreach ($planned->concat($running) as $shift) {
            $status = &$statuses[$shift->employee_id];
            $interval = ['begin' => $shift->begin, 'end' => $shift->end];
            if ($shift->end <= $timestamp) {
                if ($status['last_shift_date'] === null || $shift->begin > $status['last_shift_date']) {
                    $status['last_shift_date'] = $shift->begin;
                }
            } elseif ($shift->begin <= $timestamp) {
                $current = $status['current_shift'];
                if ($current === null || $shift->begin > $current['begin']
                    || ($shift->begin === $current['begin'] && $shift->end > $current['end'])) {
                    $status['current_shift'] = $interval;
                }
            } elseif ($status['next_shift'] === null || $shift->begin < $status['next_shift']['begin']) {
                $status['next_shift'] = $interval;
                $status['next_shift_date'] = $shift->begin;
            }
            unset($status);
        }
        return $statuses;
    }
}
