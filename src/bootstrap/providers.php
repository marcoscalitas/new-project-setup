<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Auth\Providers\FortifyServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Permission\Providers\PermissionServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
];
