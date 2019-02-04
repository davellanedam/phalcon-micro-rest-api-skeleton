<?php

use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://api.myproject.local/', 'http_errors' => false]);
        $this->credentials = 'm2ZWs+fUkNCSl/EwRaupfrRpkJDf5L4cc5RJILHjMzgifMIXplDz70yQXiCTbbvzu2BzBTPADBF83YxZQfAEPlCKfTSIPIHOY3Z6M4/1HsOKeIfBomxTBDlP6xyHNUH1VRKtIIm8ClRC8QJZvQO5I5ATHU488/W6Mx+JyE94Wceot2C57JDELvt8lY9Buu2ORZU0CLH6Ih7znOEUE3V7GodYdYNrCCm/hEwTw1V9XpHFUBNKqLjrG/Rh0KYHvIRBDfNXlKzyKJFnFnSfzvJBjk64N0smUfxrLILqaMAFVGlcs5HsItIzq0il5o4Wc1Ng7R3HbVHK8BpUyPeqguBB8Q0OnhGmlQmD4sjIpsHwJk2Db7rCQY+4xl7KxB05SMC/Qxl++I9ldXj3Hc4suAJvgowO673zwIcrrcnNb9QcJ9er0UVCV8W6mAW0wDzysWfMbbHFJ/LAdooF7UwayH3hkSh6BIzY1tMDh7FOMJ7tkH8zWFJPWThnLMgUK+f98NGjU9Ld1YwV11x2RYF5lCrx';
    }

    public function tearDown()
    {
        $this->http = null;
    }

    protected function deleteItem($id)
    {
        $response = $this->http->request('DELETE', 'users/delete/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    protected function createItem()
    {
        $response = $this->http->request('POST', 'users/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
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
            ]]);

        $this->assertEquals(201, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];
        return $id;
    }

    public function testGetUsers()
    {
        $response = $this->http->request('GET', 'users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
    }

    public function testCreateUser()
    {
        // Create new item
        $id = $this->createItem();

        // Delete just created item
        $this->deleteItem($id);
    }

    public function testCannotCreateDuplicatedUser()
    {
        // Create new item
        $id = $this->createItem();

        // creates duplicated
        $response = $this->http->request('POST', 'users/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
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
            ]]);

        $this->assertEquals(409, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('profile.ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME', $json['messages']);

        // Delete just created item
        $this->deleteItem($id);
    }

    public function testGetUserById()
    {
        $response = $this->http->request('GET', 'users/get/1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('1', $json['data']['id']);
    }

    public function testUpdateUser()
    {
        // Create new item
        $id = $this->createItem();

        // updates user
        $response = $this->http->request('PATCH', 'users/update/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
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
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('onemore@email.com', $json['data']['email']);

        // Delete just created item
        $this->deleteItem($id);
    }

    public function testDeleteUser()
    {
        // Create new item
        $id = $this->createItem();

        // Delete just created item
        $this->deleteItem($id);
    }
}
