<?php

namespace Tests\Unit\Core;

use Modules\Core\Exceptions\AuthorizationException;
use Modules\Core\Exceptions\DomainException;
use Modules\Core\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExceptionsTest extends TestCase
{
    // --- DomainException ---

    public function test_domain_exception_extends_runtime_exception(): void
    {
        $e = new DomainException('error');

        $this->assertInstanceOf(RuntimeException::class, $e);
    }

    public function test_domain_exception_carries_message(): void
    {
        $e = new DomainException('something went wrong');

        $this->assertSame('something went wrong', $e->getMessage());
    }

    // --- NotFoundException ---

    public function test_not_found_exception_extends_domain_exception(): void
    {
        $e = new NotFoundException('User');

        $this->assertInstanceOf(DomainException::class, $e);
    }

    public function test_not_found_without_id_formats_message(): void
    {
        $e = new NotFoundException('User');

        $this->assertSame('User not found.', $e->getMessage());
    }

    public function test_not_found_with_integer_id_formats_message(): void
    {
        $e = new NotFoundException('User', 42);

        $this->assertSame('User [42] not found.', $e->getMessage());
    }

    public function test_not_found_with_string_id_formats_message(): void
    {
        $e = new NotFoundException('Order', 'abc-123');

        $this->assertSame('Order [abc-123] not found.', $e->getMessage());
    }

    public function test_not_found_exception_has_code_404(): void
    {
        $e = new NotFoundException('User');

        $this->assertSame(404, $e->getCode());
    }

    // --- AuthorizationException ---

    public function test_authorization_exception_extends_domain_exception(): void
    {
        $e = new AuthorizationException();

        $this->assertInstanceOf(DomainException::class, $e);
    }

    public function test_authorization_exception_has_default_message(): void
    {
        $e = new AuthorizationException();

        $this->assertSame('This action is unauthorized.', $e->getMessage());
    }

    public function test_authorization_exception_accepts_custom_message(): void
    {
        $e = new AuthorizationException('You cannot delete this resource.');

        $this->assertSame('You cannot delete this resource.', $e->getMessage());
    }

    public function test_authorization_exception_has_code_403(): void
    {
        $e = new AuthorizationException();

        $this->assertSame(403, $e->getCode());
    }
}
