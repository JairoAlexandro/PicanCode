<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PanelControllerTest extends WebTestCase
{
    public function testPanelPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/gestion/panel');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }
}
