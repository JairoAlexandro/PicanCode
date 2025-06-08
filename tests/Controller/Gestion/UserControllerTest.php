<?php
namespace App\Tests\Controller\Gestion;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerGestionTest extends WebTestCase
{
    public function testIndexLoadsSuccessfully(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/gestion/user/');
        $this->assertResponseIsSuccessful();
    }

    public function testEditNonExistentUserReturns404(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/gestion/user/999/edit');
        $this->assertResponseStatusCodeSame(404);
    }

}
