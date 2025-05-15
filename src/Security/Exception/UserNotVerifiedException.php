<?php
namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserNotVerifiedException extends CustomUserMessageAuthenticationException
{
    public function __construct(string $message = 'Debes verificar tu email antes de iniciar sesión.')
    {
        parent::__construct($message);
    }
} 