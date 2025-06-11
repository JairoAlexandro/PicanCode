<?php

namespace App\Tests\Controller\Front;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends WebTestCase
{
    private $client;
    private $em;   
    private User $user;

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
            ->setUsername('puser')
            ->setEmail('puser@example.com')
            ->setPassword('pass')
            ->setCreatedAt(new \DateTime());
        $this->em->persist($this->user);
        $this->em->flush();
    }

    public function testChangeAvatarWithoutFileShowsWarning(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('POST', '/user/'.$this->user->getId().'/avatar');
        $this->assertResponseRedirects('/user/'.$this->user->getId());

        $this->client->followRedirect();
        $this->assertStringContainsString(
            'No seleccionaste ningún archivo.',
            $this->client->getResponse()->getContent()
        );
    }

    public function testEditBioGetAjaxReturnsJson(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('GET', '/user/'.$this->user->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('apiUrl', $data);
    }

    public function testEditBioPostAjaxInvalid(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('POST', '/user/'.$this->user->getId().'/edit', []);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $errors = json_decode($this->client->getResponse()->getContent(), true)['errors'];
        $this->assertIsArray($errors);
    }

    public function testEditBioPostAjaxValid(): void
    {
        $this->client->loginUser($this->user);

        $this->client->xmlHttpRequest(
            'POST',
            '/user/'.$this->user->getId().'/edit',
            ['bio' => 'Nueva biografía']
        );

    
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($json['success'] ?? false);
        $this->assertSame([], $json['errors']);
    }
}
