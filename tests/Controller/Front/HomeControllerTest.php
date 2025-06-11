<?php

namespace App\Tests\Controller;

use App\Controller\Front\HomeController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class HomeControllerTest extends KernelTestCase
{
    private $container;
    private EntityManagerInterface $em;
    private HomeController $controller;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container  = static::getContainer();
        $this->em         = $this->container->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM App\\Entity\\User u')->execute();

        $this->controller = new HomeController();
        $this->controller->setContainer($this->container);
    }

    public function testHomeRedirectsForAnonymous(): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(null);

        /** @var RedirectResponse $response */
        $response = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getTargetUrl());
    }

    public function testHomeRedirectsForAuthenticated(): void
    {
        $user = new User();
        $user
            ->setUsername('tester')
            ->setEmail('tester@example.com')
            ->setPassword('irrelevant')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        /** @var RedirectResponse $response */
        $response = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/posts', $response->getTargetUrl());
    }
}
