<?php
namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Follower;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerAjaxTest extends WebTestCase
{
    private $client;
    private $em;
    private $user;
    private $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $container    = static::getContainer();
        $this->em     = $container->get('doctrine')->getManager();


        $this->em->createQuery('DELETE App\\Entity\\Post p')->execute();
        $this->em->createQuery('DELETE App\\Entity\\User u')->execute();

        $this->user = (new User())
            ->setUsername('user1')
            ->setEmail('user1@example.com')
            ->setPassword('pass')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($this->user);

        $this->otherUser = (new User())
            ->setUsername('user2')
            ->setEmail('user2@example.com')
            ->setPassword('pass')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($this->otherUser);

        $this->em->flush();
    }

    public function testGetProfileAnonymous(): void
    {
        $this->client->request('GET', '/user/' . $this->otherUser->getId());
        $this->assertResponseRedirects('/login');
    }

    public function testGetProfileAuthenticated(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/user/' . $this->otherUser->getId());

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('user2', $content);
    }

    public function testProfileNotFound(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/user/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testInvalidCsrfReturns403(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('POST', '/user/' . $this->otherUser->getId(), [
            'follow' => '1',
            '_token' => 'invalid',
        ]);

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertContains('Token CSRF inválido', $data['errors']);
    }
}
