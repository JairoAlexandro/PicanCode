<?php
namespace App\Tests\Controller\Gestion;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerGestionTest extends WebTestCase
{
    public function testIndexLoadsSuccessfully(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/gestion/post/');
        $this->assertResponseIsSuccessful();
    }

    public function testEditNonExistentPostReturns404(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/gestion/post/999/edit');
        $this->assertResponseStatusCodeSame(404);
    }

}
