<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require 'lib/RestClientLib/RestClient.php';
require 'lib/RestClientLib/CurlHttpResponse.php';

final class RestClientTest extends TestCase
{
    public function testGetResponse(): void
    {
        $restClient = new App\RestClientLib\RestClient();
        $restClient->setRemoteHost('google.com')
            ->setUriBase('/')
            ->setUseSsl(false)
            ->setUseSslTestMode(false)
            ->setBasicAuthCredentials('username', 'password')
            ->setHeaders(array('Accept' => 'text/html'));

        $this->assertInstanceOf(
            App\RestClientLib\RestClient::class,
            $restClient
        );
        $this->assertEquals(200, $restClient->get('')->getHttpCode());
    }

}