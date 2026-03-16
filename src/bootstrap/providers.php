<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Admin\Providers\AdminServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Auth\Providers\FortifyServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Permission\Providers\PermissionServiceProvider::class,
    Modules\Settings\Providers\SettingsServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Web\Providers\WebServiceProvider::class,
];
