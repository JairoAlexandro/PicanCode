<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
        // Limpiar usuarios
        $this->em->createQuery('DELETE FROM App\\Entity\\User u')->execute();
    }

    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/login');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response->isSuccessful(),
            'Login page should load successfully.'
        );
        // Comprueba que los campos de login existen
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testLoginRedirectsWhenAuthenticated(): void
    {
        $user = (new User())
            ->setUsername('test')
            ->setEmail('test@example.com')
            ->setPassword('irrelevant')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/login');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'Authenticated user should be redirected from login.'
        );
        $this->assertStringContainsString(
            '/posts',
            $this->client->getResponse()->headers->get('Location')
        );
    }

    public function testLogoutRedirects(): void
    {
        $this->client->request('GET', '/logout');
        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'Logout path should redirect.'
        );
    }

    public function testGestionRedirectsToLogin(): void
    {
        $this->client->request('GET', '/gestion');
        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'Gestion home should redirect.'
        );
        $this->assertStringContainsString(
            '/gestion/login',
            $this->client->getResponse()->headers->get('Location')
        );
    }

    public function testGestionLoginPageLoads(): void
    {
        $this->client->request('GET', '/gestion/login');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response->isSuccessful(),
            'Gestion login page should load.'
        );
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testGestionLogoutRedirects(): void
    {
        $this->client->request('GET', '/gestion/logout');
        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            'Gestion logout should redirect.'
        );
    }

    public function testLogoutMethodsAreCallable(): void
    {
        $controller = new \App\Controller\SecurityController();
        $controller->logoutApp();
        $controller->logoutGestion();
        $this->assertTrue(true, 'Logout methods should be callable and do nothing.');
    }
}
