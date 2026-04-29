<?php

namespace Modules\Core\Exceptions;

class AuthorizationException extends DomainException
{
    public function __construct(string $message = 'This action is unauthorized.')
    {
        parent::__construct($message, 403);
    }
}
