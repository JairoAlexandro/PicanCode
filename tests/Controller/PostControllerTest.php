<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testIndexRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts');
        $this->assertResponseRedirects('/login');
    }

    public function testNewRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/new');
        $this->assertResponseRedirects('/login');
    }

    public function testShowRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/999999');
        $this->assertResponseRedirects('/login');
    }

    public function testEditRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/1/edit');
        $this->assertResponseRedirects('/login');
    }

    public function testDeleteRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('POST', '/posts/1/delete');
        $this->assertResponseRedirects('/login');
    }
}
