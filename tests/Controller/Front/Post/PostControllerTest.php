<?php

namespace App\Tests\Controller\Front;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private User $user;
    private Post $post;

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

        $this->user = (new User())
            ->setUsername('u1')
            ->setEmail('u1@example.com')
            ->setPassword('irrelevant')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($this->user);

        $this->post = (new Post())
            ->setUser($this->user)
            ->setTitle('T')
            ->setContent('C')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setIsPublished(true);
        $this->em->persist($this->post);
        $this->em->flush();
    }

    public function testIndexRedirectsAnonymousAndLoadsForAuthenticated(): void
    {
        $this->client->request('GET', '/posts');
        $this->assertResponseRedirects('/login');

        $this->client->loginUser($this->user);
        $this->client->request('GET', '/posts');
        $this->assertResponseIsSuccessful();
    }

    public function testIndexReturnsJsonAjax(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('GET', '/posts?view=recent');
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('recent', $json['view']);
        $this->assertIsArray($json['posts']);
    }


    public function testShowCommentLike(): void
    {
        $this->client->loginUser($this->user);

        $this->client->xmlHttpRequest('GET', '/posts/'.$this->post->getId());
        $data = json_decode($this->client->getResponse()->getContent(), true)['data'];
        $this->assertSame($this->post->getId(), $data['id']);

        $this->client->xmlHttpRequest(
            'POST',
            '/posts/'.$this->post->getId(),
            [], [],
            ['CONTENT_TYPE'=>'application/json','X-Requested-With'=>'XMLHttpRequest'],
            json_encode(['content'=>'Hi'])
        );
        $c = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($c['success']);
        $this->assertArrayHasKey('comment', $c);

        $this->client->xmlHttpRequest('POST', '/posts/'.$this->post->getId());
        $l1 = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($l1['liked']);
        $this->client->xmlHttpRequest('POST', '/posts/'.$this->post->getId());
        $l2 = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($l2['liked']);
    }

}
