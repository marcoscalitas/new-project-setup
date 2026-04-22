<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sync limit
    |--------------------------------------------------------------------------
    | Number of records below which exports are generated synchronously.
    | Above this limit, exports are dispatched to the queue.
    */
    'sync_limit' => env('EXPORT_SYNC_LIMIT', 5000),

    /*
    |--------------------------------------------------------------------------
    | Expiration hours
    |--------------------------------------------------------------------------
    | Hours before async export files are deleted from storage.
    */
    'expiration_hours' => env('EXPORT_EXPIRATION_HOURS', 24),
];
