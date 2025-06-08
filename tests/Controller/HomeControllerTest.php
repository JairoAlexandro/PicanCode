<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomeRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertTrue(
            $client->getResponse()->isRedirection(),
            'Home page should redirect for both anonymous and authenticated users.'
        );
    }
}
