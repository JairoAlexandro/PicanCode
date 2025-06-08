<?php

namespace App\Tests\Security\Exception;

use App\Security\Exception\UserNotVerifiedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserNotVerifiedExceptionTest extends TestCase
{
    public function testDefaultMessageAndType(): void
    {
        $exception = new UserNotVerifiedException();

        $this->assertInstanceOf(
            CustomUserMessageAuthenticationException::class,
            $exception
        );

        $this->assertSame(
            'Debes verificar tu email antes de iniciar sesión.',
            $exception->getMessage()
        );
    }

    public function testCustomMessage(): void
    {
        $custom = 'Mensaje personalizado';
        $exception = new UserNotVerifiedException($custom);

        $this->assertSame($custom, $exception->getMessage());
    }
}
