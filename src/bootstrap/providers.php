<?php

return [
    App\Providers\AppServiceProvider::class,

    Modules\Admin\Providers\AdminServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Web\Providers\WebServiceProvider::class,
];
