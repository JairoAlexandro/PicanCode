<?php

namespace App\Tests\Security;

use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginFormAuthenticatorTest extends TestCase
{
    private LoginFormAuthenticator $authenticator;
    private \PHPUnit\Framework\MockObject\MockObject $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator   = $this->createMock(UrlGeneratorInterface::class);
        $this->authenticator  = new LoginFormAuthenticator($this->urlGenerator);
    }

    public function testAuthenticateCreatesPassportAndStoresLastUsername(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request([], [
            '_username'   => 'alice',
            '_password'   => 'secret',
            '_csrf_token' => 'csrf123',
        ]);
        $request->setSession($session);

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(Passport::class, $passport);
        $this->assertSame('alice', $session->get(Security::LAST_USERNAME));
    }

    public function testOnAuthenticationSuccessRedirectsToTargetPath(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('_security.main.target_path', '/foo/bar');
        $request = new Request();
        $request->setSession($session);

        $token = $this->createMock(TokenInterface::class);

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/foo/bar', $response->getTargetUrl());
    }

    public function testOnAuthenticationSuccessRedirectsToDefaultWhenNoTargetPath(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);

        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('post_index')
            ->willReturn('/default');

        $token = $this->createMock(TokenInterface::class);

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'irrelevant');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/default', $response->getTargetUrl());
    }

    public function testStartRedirectsToLogin(): void
    {
        // Preparamos el Request y la excepción de autenticación
        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with(LoginFormAuthenticator::LOGIN_ROUTE)
            ->willReturn('/login-url');

        $request = new Request();
        $authException = new AuthenticationException();

        // El método público start() usa internamente getLoginUrl()
        $response = $this->authenticator->start($request, $authException);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login-url', $response->getTargetUrl());
    }
}
