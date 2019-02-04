<?php

use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://api.myproject.local/', 'http_errors' => false]);
        $this->credentials = $this->getToken();
    }

    public function tearDown()
    {
        $this->http = null;
    }

    protected function getJSON($response)
    {
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        return $json;
    }

    protected function getToken()
    {
        $credentials = base64_encode('admin:admin1234');

        $response = $this->http->request('POST', 'authenticate', [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
        $this->assertEquals('1', $json['data']['user']['id']);
        $this->assertEquals('admin', $json['data']['user']['username']);
        return $json['data']['token'];
    }

    protected function buildGetRequest($endpoint)
    {
        $response = $this->http->request('GET', $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);
        return $response;
    }

    protected function buildPatchRequest($endpoint, $params)
    {
        $response = $this->http->request('PATCH', $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => $params]);
        return $response;
    }

    public function testGetProfile()
    {
        $response = $this->buildGetRequest('profile');
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('1', $json['data']['id']);
        $this->assertEquals('admin', $json['data']['username']);
    }

    public function testUpdateProfile()
    {
        $params = [
            'email' => 'asd@asd.com',
            'firstname' => 'Other name',
            'lastname' => 'Lastname',
            'phone' => '12312312',
            'mobile' => '31312312',
            'address' => 'Calle 10',
            'city' => 'Bogotá',
            'country' => 'Colombia',
            'birthday' => '1979-01-01',
        ];
        $response = $this->buildPatchRequest('profile/update', $params);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('1', $json['data']['id']);
        $this->assertEquals('admin', $json['data']['username']);
        $this->assertEquals('Other name', $json['data']['firstname']);
    }

    public function testChangePassword()
    {
        $params = [
            'current_password' => 'admin1234',
            'new_password' => 'admin12345',
        ];
        $response = $this->buildPatchRequest('profile/change-password', $params);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('change-password.PASSWORD_SUCCESSFULLY_UPDATED', $json['messages']);

        // restore default password
        $params = [
            'current_password' => 'admin12345',
            'new_password' => 'admin1234',
        ];
        $response = $this->buildPatchRequest('profile/change-password', $params);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('change-password.PASSWORD_SUCCESSFULLY_UPDATED', $json['messages']);
    }
}
