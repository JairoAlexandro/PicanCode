<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Follower;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    private $client;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $em->createQuery('DELETE App\\Entity\\Follower f')->execute();
        $em->createQuery('DELETE App\\Entity\\User u')->execute();

        $this->user = (new User())
            ->setUsername('simpleuser')
            ->setEmail('simple@example.com')
            ->setPassword('password')
            ->setCreatedAt(new \DateTime());

        $em->persist($this->user);
        $em->flush();
    }

    public function testAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', '/user/' . $this->user->getId());
        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedSeesProfile(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/user/' . $this->user->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testNotFoundReturns404(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/user/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
