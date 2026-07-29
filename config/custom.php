<?php

return [
    'NRKAPIKEY' => env('NRKAPIKEY', ''),
    'NRKAPISERVER' => env('NRKAPISERVER', ''),
    'NRK_PLAN_IDS' => [82, 3316],
    'NRK_VEHICLE_STATUS_CACHE_SECONDS' => (int) env('NRK_VEHICLE_STATUS_CACHE_SECONDS', 300),
];
