<?php

namespace Modules\Media\Policies;

use Modules\Core\Policies\BasePolicy;

class MediaPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'media';
    }
}
