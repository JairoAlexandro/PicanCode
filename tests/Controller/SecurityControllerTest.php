<?php
// tests/Controller/Front/SecurityControllerTest.php

namespace App\Tests\Controller\Front;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private $em;  

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\Comment c')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Like l')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Post p')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Follower f')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
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
        $this->assertResponseRedirects('/posts');
    }

    public function testLogoutRedirects(): void
    {
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }

    public function testGestionRedirectsToLogin(): void
    {
        $this->client->request('GET', '/gestion');
        $this->assertResponseRedirects('/gestion/login');
    }

    public function testGestionLoginPageLoads(): void
    {
        $this->client->request('GET', '/gestion/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testGestionLogoutRedirects(): void
    {
        $this->client->request('GET', '/gestion/logout');
        $this->assertResponseRedirects();
    }

    public function testLogoutMethodsAreCallable(): void
    {
        $controller = new \App\Controller\SecurityController();
        $controller->logoutApp();
        $controller->logoutGestion();
        $this->assertTrue(true);
    }
}
