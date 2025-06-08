<?php
// tests/Controller/HomeControllerTest.php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
        // Limpiar usuarios antes de cada test
        $this->em->createQuery('DELETE FROM App\\Entity\\User u')->execute();
    }

    public function testHomeRedirectsForAnonymous(): void
    {
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response->isRedirect(),
            'Anonymous user should be redirected from home.'
        );
        $this->assertStringContainsString(
            '/login',
            $response->headers->get('Location'),
            'Anonymous should be sent to the login page.'
        );
    }

    public function testHomeRedirectsForAuthenticated(): void
    {
        $user = new User();
        $user->setUsername('tester')
             ->setEmail('tester@example.com')
             ->setPassword('irrelevant')
             ->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response->isRedirect(),
            'Authenticated user should be redirected from home.'
        );
        $this->assertStringContainsString(
            '/posts',
            $response->headers->get('Location'),
            'Authenticated should be sent to the posts index.'
        );
    }
}