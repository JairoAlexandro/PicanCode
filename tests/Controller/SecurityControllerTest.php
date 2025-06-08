<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageLoads()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testGestionRedirectsToLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/gestion');
        $this->assertResponseRedirects('/gestion/login');
    }

    public function testGestionLoginPageLoads()
    {
        $client = static::createClient();
        $client->request('GET', '/gestion/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
