<?php

use PHPUnit\Framework\TestCase;

class CitiesTest extends TestCase
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
        $response = $this->buildGetOrDeleteRequestById('DELETE', 'cities/delete/', $id);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.DELETED_SUCCESSFULLY', $json['messages']);
    }

    protected function createItem()
    {
        $params = [
            'name' => 'Girón',
            'country' => 'Colombia',
        ];
        $response = $this->buildPostRequest('cities/create', $params);
        $this->assertEquals(201, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.CREATED_SUCCESSFULLY', $json['messages']);
        $id = $json['data']['id'];
        return $id;
    }

    public function testGetCities()
    {
        $response = $this->buildGetRequest('cities');
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.SUCCESSFUL_REQUEST', $json['messages']);
    }

    public function testCreateCity()
    {
        $id = $this->createItem();
        $this->deleteItem($id);
    }

    public function testCannotCreateDuplicatedCity()
    {
        $params = [
            'name' => 'Bogotá',
            'country' => 'Colombia',
        ];
        $response = $this->buildPostRequest('cities/create', $params);
        $this->assertEquals(409, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME', $json['messages']);
    }

    public function testGetCityById()
    {
        $response = $this->buildGetOrDeleteRequestById('GET', 'cities/get/', 1);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('Bogotá', $json['data']['name']);
    }

    public function testUpdateCity()
    {
        $id = $this->createItem();
        $params = [
            'name' => 'Girón2',
            'country' => 'Colombia2',
        ];
        $response = $this->buildPatchRequest('cities/update/', $params, $id);
        $this->assertEquals(200, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('Girón2', $json['data']['name']);
        $this->deleteItem($id);
    }

    public function testCannotUpdateCityThatAlreadyExists()
    {
        $id = $this->createItem();
        $params = [
            'name' => 'Bogotá',
            'country' => 'Colombia',
        ];
        $response = $this->buildPatchRequest('cities/update/', $params, $id);
        $this->assertEquals(409, $response->getStatusCode());
        $json = $this->getJSON($response);
        $this->assertEquals('common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME', $json['messages']);
        $this->deleteItem($id);
    }

    public function testDeleteCity()
    {
        $id = $this->createItem();
        $this->deleteItem($id);
    }
}
