<?php

namespace App\Tests\Controller\Front;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerAjaxTest extends WebTestCase
{
    private $client;
    private $em;            
    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\Comment c')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Like l')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Post p')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Follower f')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

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
        $this->client->request('GET', '/user/'.$this->otherUser->getId());
        $this->assertResponseRedirects('/login');
    }

    public function testGetProfileAuthenticated(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/user/'.$this->otherUser->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('user2', $this->client->getResponse()->getContent());
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
        $this->client->xmlHttpRequest('POST', '/user/'.$this->otherUser->getId(), [
            'follow' => '1',
            '_token' => 'invalid',
        ]);

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Token CSRF inválido', implode(' ', $data['errors']));
    }
}
