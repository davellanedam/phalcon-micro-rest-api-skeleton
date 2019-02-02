<?php

use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
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

    public function testGetProfile()
    {
        $response = $this->http->request('GET', 'profile', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('1', $json['data']['id']);
        $this->assertEquals('admin', $json['data']['username']);
    }

    public function testUpdateProfile()
    {
        $response = $this->http->request('PATCH', 'profile/update', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'email' => 'asd@asd.com',
                'firstname' => 'Other name',
                'lastname' => 'Lastname',
                'phone' => '12312312',
                'mobile' => '31312312',
                'address' => 'Calle 10',
                'city' => 'Bogotá',
                'country' => 'Colombia',
                'birthday' => '1979-01-01',
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('1', $json['data']['id']);
        $this->assertEquals('admin', $json['data']['username']);
        $this->assertEquals('Other name', $json['data']['firstname']);
    }

    public function testChangePassword()
    {
        $response = $this->http->request('PATCH', 'profile/change-password', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'current_password' => 'admin1234',
                'new_password' => 'admin12345',
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('change-password.PASSWORD_SUCCESSFULLY_UPDATED', $json['messages']);

        // restore default password
        $response = $this->http->request('PATCH', 'profile/change-password', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->credentials,
            ],
            'form_params' => [
                'current_password' => 'admin12345',
                'new_password' => 'admin1234',
            ]]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json; charset=UTF-8", $contentType);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('change-password.PASSWORD_SUCCESSFULLY_UPDATED', $json['messages']);
      }
}
