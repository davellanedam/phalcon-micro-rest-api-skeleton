<?php

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://api.myproject.local/', 'http_errors' => false]);
    }

    public function tearDown()
    {
        $this->http = null;
    }

    public function testLogin()
    {
        $credentials = base64_encode('admin:admin1234');

        $response = $this->http->request('POST', 'authenticate', [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
        $this->assertEquals('1', $json['data']['user']['id']);
        $this->assertEquals('admin', $json['data']['user']['username']);
    }

    public function testLoginUserNotRegistered()
    {
        $credentials = base64_encode('admin1234:admin1234');

        $response = $this->http->request('POST', 'authenticate', [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]]);

        $this->assertEquals(404, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('login.USER_IS_NOT_REGISTERED', $json['messages']);
    }

    public function testLoginWrongPassword()
    {
        $credentials = base64_encode('admin:admin12345');

        $response = $this->http->request('POST', 'authenticate', [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]]);

        $this->assertEquals(400, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('login.WRONG_USER_PASSWORD', $json['messages']);
    }
}
