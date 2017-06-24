<?php

namespace Tests;

use MercadoPago\Exceptions\MercadoPagoException;
use MercadoPago\Http\CurlClient;
use MercadoPago\MercadoPago;
use PHPUnit\Framework\TestCase;

class MercadoPagoCurlTest extends TestCase
{
    /** @test **/
    public function itCanCreateInstance()
    {
        $client = $this->getMockBuilder(CurlClient::class)->getMock();
        new MercadoPago($client);
    }

    /** @test **/
    public function itCanGetClient()
    {
        $client = $this->getMockBuilder(CurlClient::class)->getMock();
        $mp = new MercadoPago($client);
        $this->assertEquals($client, $mp->getClient());
    }

    /**
     * @test
     * @vcr curl_it_can_request_access_token
     */
    public function itCanRequestAccessToken()
    {
        $mp = new MercadoPago(new CurlClient());
        $mp->setCredentials('CLIENT_ID', 'CLIENT_SECRET');
        $this->assertEquals(
            'SOME_ACCESS_TOKEN',
            $mp->getAccessToken()
        );
    }

    /**
     * @test
     * @vcr curl_it_can_request_access_token_error
     * @expectedExceptionMessage Invalid client_id
     * @expectedExceptionCode 400
     * @expectedException \MercadoPago\Exceptions\MercadoPagoException
     */
    public function itCanRequestAccessTokenError()
    {
        $mp = new MercadoPago(new CurlClient());
        $mp->setCredentials('WRONG_CLIENT_ID', 'WRONG_CLIENT_SECRET');
        $mp->getAccessToken();
    }

    /**
     * @test
     * @vcr curl_it_can_search_payments
     */
    public function itCanSearchPayments()
    {
        $mp = new MercadoPago(new CurlClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $response = $mp->searchPayments(['country' => 'AR'], 0, 10);
        $this->assertEquals(['total' => 1, 'offset' => 0, 'limit' => 10], $response['paging']);
        $this->assertEquals(1, $response['results'][0]['collection']['id']);
    }

    /**
     * @test
     * @vcr curl_it_can_create_preference
     */
    public function itCanCreatePreference()
    {
        $mp = new MercadoPago(new CurlClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $preference = $mp->createPreference([
            'items' => [
                [
                    'title' => 'foo',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => 123.4431342312312312312,
                ],
            ],
        ]);

        $this->assertEquals('SOME_ID', $preference['id']);
    }

    /**
     * @test
     * @vcr curl_it_can_create_preference_error
     */
    public function itCanCreatePreferenceError()
    {
        //
        $mp = new MercadoPago(new CurlClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        try {
            $mp->createPreference([
                'items' => [
                    [
                        'title' => 'foo',
                        'quantity' => 1,
                        'currency_id' => 'XUY',
                        'unit_price' => 'asdasd',
                    ],
                ],
            ]);

            $this->fail('Exception should have been thrown');
        } catch (MercadoPagoException $exception) {
            $this->assertEquals('currency_id invalid', $exception->getMessage());
        }
    }

    /**
     * @test
     * @vcr curl_it_can_get_preference
     */
    public function itCanGetPreferenceError()
    {
        $mp = new MercadoPago(new CurlClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $preference = $mp->getPreference('SOME_ID');

        $this->assertEquals('SOME_ID', $preference['id']);
        $this->assertEquals('foo', $preference['items'][0]['title']);
    }
}
