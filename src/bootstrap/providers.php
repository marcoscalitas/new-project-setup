<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    Modules\Settings\Providers\SettingsServiceProvider::class,
    Modules\ActivityLog\Providers\ActivityLogServiceProvider::class,
    Modules\Identity\Providers\IdentityServiceProvider::class,
    Modules\Identity\Providers\FortifyServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Permission\Providers\PermissionServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Export\Providers\ExportServiceProvider::class,
];
