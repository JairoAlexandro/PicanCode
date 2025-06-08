<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageLoadsForAnonymous()
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
    }

    public function testAjaxInvalidSubmissionReturnsBadRequest()
    {
        $client = static::createClient();
        $client->xmlHttpRequest('POST', '/register', []);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
    }
}
