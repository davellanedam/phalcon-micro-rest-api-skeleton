<?php

use PHPUnit\Framework\TestCase;

class CitiesTest extends TestCase
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

    public function testGetCities()
    {
        $response = $this->http->request('GET', 'cities', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
    }

    public function testCreateCity()
    {
        $response = $this->http->request('POST', 'cities/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Girón',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(201, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];

        // Delete just created item
        $response = $this->http->request('DELETE', 'cities/delete/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    public function testCannotCreateDuplicatedCity()
    {
        $response = $this->http->request('POST', 'cities/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Bogotá',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(409, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME', $json['messages']);
    }

    public function testGetCityById()
    {
        $response = $this->http->request('GET', 'cities/get/1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('Bogotá', $json['data']['name']);
    }

    public function testUpdateCity()
    {
        // Create city
        $response = $this->http->request('POST', 'cities/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Girón',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(201, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];

        // updates city
        $response = $this->http->request('PATCH', 'cities/update/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Girón2',
                'country' => 'Colombia2',
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('Girón2', $json['data']['name']);

        // Delete just created item
        $response = $this->http->request('DELETE', 'cities/delete/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    public function testCannotUpdateCityThatAlreadyExists()
    {
        // Create city
        $response = $this->http->request('POST', 'cities/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Girón',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(201, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];

        // updates city
        $response = $this->http->request('PATCH', 'cities/update/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Bogotá',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(409, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME', $json['messages']);

        // Delete just created item
        $response = $this->http->request('DELETE', 'cities/delete/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    public function testDeleteCity()
    {
        $response = $this->http->request('POST', 'cities/create', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'name' => 'Girón',
                'country' => 'Colombia',
            ]]);

        $this->assertEquals(201, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];

        // Delete just created item
        $response = $this->http->request('DELETE', 'cities/delete/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }
}
