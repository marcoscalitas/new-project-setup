<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    Modules\ActivityLog\Providers\ActivityLogServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Auth\Providers\FortifyServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Permission\Providers\PermissionServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
];
