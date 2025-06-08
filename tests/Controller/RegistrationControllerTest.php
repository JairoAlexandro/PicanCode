<?php
// tests/Controller/RegistrationControllerTest.php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM App\\Entity\\User u')->execute();
    }

    public function testRegisterPageLoadsForAnonymous(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="registration_form"]');
    }

    public function testRegisterRedirectsWhenLoggedIn(): void
    {
        $user = (new User())
            ->setEmail('a@b.com')
            ->setUsername('test')
            ->setPassword('irrelevant');
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/register');
        $this->assertResponseRedirects('/posts');
    }

    public function testNormalInvalidSubmissionShowsErrors(): void
    {
        $crawler = $this->client->request('POST', '/register', [
            'registration_form' => [
                'email'         => 'not-an-email',
                'username'      => '',
                'plainPassword' => ['first' => 'short', 'second' => 'short'],

            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="registration_form"]');

        $this->assertSelectorExists('input[name="registration_form[email]"][value="not-an-email"]');
    }

    public function testAjaxInvalidSubmissionReturnsBadRequestAndMessage(): void
    {
        $this->client->xmlHttpRequest('POST', '/register', []);
        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $json);
        $this->assertFalse($json['success']);
        $this->assertArrayHasKey('message', $json);
        $this->assertIsString($json['message']);
    }

    public function testAjaxValidSubmissionReturnsSuccess(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $token   = $crawler
            ->filter('input[name="registration_form[_token]"]')
            ->attr('value');

        $this->client->xmlHttpRequest('POST', '/register', [
            'registration_form' => [
                'email'         => 'user@example.com',
                'username'      => 'newuser',
                'plainPassword' => ['first' => 'ValidPass1', 'second' => 'ValidPass1'],
                '_token'        => $token,
            ],
        ]);

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $json = json_decode($response->getContent(), true);
        $this->assertTrue($json['success']);

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => 'user@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user->getUsername());
    }
}
