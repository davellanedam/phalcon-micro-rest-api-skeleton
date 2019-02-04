<?php

use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
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

    protected function buildGetOrDeleteRequestById($method, $endpoint, $id)
    {
        $response = $this->http->request($method, $endpoint . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);
        return $response;
    }

    protected function buildPostRequest($endpoint, $params)
    {
        $response = $this->http->request('POST', $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => $params]);
        return $response;
    }

    protected function buildPatchRequest($endpoint, $params, $id)
    {
        $response = $this->http->request('PATCH', $endpoint . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => $params]);
        return $response;
    }

    protected function deleteItem($id)
    {
        $response = $this->buildGetOrDeleteRequestById('DELETE', 'users/delete/', $id);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    protected function createItem()
    {
        $params = [
            'email' => 'another@email.com',
            'new_password' => 'test123',
            'username' => 'admintest',
            'firstname' => 'My name',
            'lastname' => 'My last name',
            'level' => 'Superuser',
            'phone' => '12312312',
            'mobile' => '31312312',
            'address' => 'Calle 10',
            'city' => 'Bogotá',
            'country' => 'Colombia',
            'birthday' => '1979-01-01',
            'authorised' => '1',
        ];
        $response = $this->buildPostRequest('users/create', $params);
        $this->assertEquals(201, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];
        return $id;
    }

    public function testGetUsers()
    {
        $response = $this->buildGetRequest('users');
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
    }

    public function testCreateUser()
    {
        $id = $this->createItem();
        $this->deleteItem($id);
    }

    public function testCannotCreateDuplicatedUser()
    {
        $id = $this->createItem();
        $params = [
            'email' => 'another@email.com',
            'new_password' => 'test123',
            'username' => 'admintest',
            'firstname' => 'My name',
            'lastname' => 'My last name',
            'level' => 'Superuser',
            'phone' => '12312312',
            'mobile' => '31312312',
            'address' => 'Calle 10',
            'city' => 'Bogotá',
            'country' => 'Colombia',
            'birthday' => '1979-01-01',
            'authorised' => '1',
        ];
        $response = $this->buildPostRequest('users/create', $params);
        $this->assertEquals(409, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('profile.ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME', $json['messages']);
        $this->deleteItem($id);
    }

    public function testGetUserById()
    {
        $response = $this->buildGetOrDeleteRequestById('GET', 'users/get/', 1);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('1', $json['data']['id']);
    }

    public function testUpdateUser()
    {
        $id = $this->createItem();
        $params = [
                'email' => 'onemore@email.com',
                'new_password' => 'test123',
                'username' => 'admintest',
                'firstname' => 'Other name',
                'lastname' => 'Other last name',
                'level' => 'Superuser',
                'phone' => '7654333',
                'mobile' => '234568',
                'address' => 'Calle 11',
                'city' => 'Bogotá',
                'country' => 'Colombia',
                'birthday' => '1979-01-01',
                'authorised' => '1',
        ];
        $response = $this->buildPatchRequest('users/update/', $params, $id);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('onemore@email.com', $json['data']['email']);
        $this->deleteItem($id);
    }

    public function testDeleteUser()
    {
        $id = $this->createItem();
        $this->deleteItem($id);
    }
}
