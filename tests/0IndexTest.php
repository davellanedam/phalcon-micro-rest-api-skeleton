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

    protected function postLogin($credentials)
    {
        $response = $this->http->request('POST', 'authenticate', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($credentials),
            ]]);
        return $response;
    }

    protected function getJSON($response)
    {
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        return $json;
    }

    public function testLogin()
    {
        $response = $this->postLogin('admin:admin1234');
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
        $this->assertEquals('1', $json['data']['user']['id']);
        $this->assertEquals('admin', $json['data']['user']['username']);
    }

    public function testLoginUserNotRegistered()
    {
        $response = $this->postLogin('admin1234:admin1234');
        $this->assertEquals(404, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('login.USER_IS_NOT_REGISTERED', $json['messages']);
    }

    public function testLoginWrongPassword()
    {
        $response = $this->postLogin('admin:admin12345');
        $this->assertEquals(400, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('login.WRONG_USER_PASSWORD', $json['messages']);
    }
}
