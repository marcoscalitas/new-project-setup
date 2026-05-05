<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    Modules\Settings\Providers\SettingsServiceProvider::class,
    Modules\AuditLog\Providers\AuditLogServiceProvider::class,
    Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    Modules\Authentication\Providers\FortifyServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Authorization\Providers\AuthorizationServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Export\Providers\ExportServiceProvider::class,
];
