<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();

        $this->em->createQuery('DELETE App\\Entity\\Follower f')->execute();
        $this->em->createQuery('DELETE App\\Entity\\User u')->execute();

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
        $this->client->request('POST', '/user/' . $this->user->getId() . '/avatar');
        $this->assertResponseRedirects('/user/' . $this->user->getId());
        $this->client->followRedirect();
        $this->assertStringContainsString('No seleccionaste ningún archivo.', $this->client->getResponse()->getContent());
    }

    public function testEditBioGetAjaxReturnsJson(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('GET', '/user/' . $this->user->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('apiUrl', $data);
    }

    public function testEditBioPostAjaxInvalid(): void
    {
        $this->client->loginUser($this->user);
        $this->client->xmlHttpRequest('POST', '/user/' . $this->user->getId() . '/edit', []);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $errors = json_decode($this->client->getResponse()->getContent(), true)['errors'];
        $this->assertIsArray($errors);
    }
}
