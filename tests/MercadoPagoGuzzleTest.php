<?php

namespace Tests;

use MercadoPago\Exceptions\MercadoPagoException;
use MercadoPago\Http\GuzzleClient;
use MercadoPago\Http\Response;
use MercadoPago\MercadoPago;
use PHPUnit\Framework\TestCase;

class MercadoPagoGuzzleTest extends TestCase
{
    /** @test **/
    public function itCanCreateInstance()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();
        new MercadoPago($client);
    }

    /** @test **/
    public function itCanGetClient()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();
        $mp = new MercadoPago($client);
        $this->assertEquals($client, $mp->getClient());
    }

    /**
     * @test
     * @vcr guzzle_it_can_request_access_token
     */
    public function itCanRequestAccessToken()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setCredentials('CLIENT_ID', 'CLIENT_SECRET');
        $this->assertEquals(
            'SOME_ACCESS_TOKEN',
            $mp->getAccessToken()
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Client ID and Client Secret are required to request a new access token.
     */
    public function itCantRequestTokensWithoutCredentials()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->getAccessToken();
    }

    /**
     * @test
     * @vcr guzzle_it_can_request_access_token_error
     * @expectedExceptionMessage Invalid client_id
     * @expectedExceptionCode 400
     * @expectedException \MercadoPago\Exceptions\MercadoPagoException
     */
    public function itCanRequestAccessTokenError()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setCredentials('WRONG_CLIENT_ID', 'WRONG_CLIENT_SECRET');
        $mp->getAccessToken();
    }

    /**
     * @test
     * @vcr guzzle_it_can_search_payments
     */
    public function itCanSearchPayments()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $response = $mp->searchPayments(['country' => 'AR'], 0, 10);
        $this->assertEquals(['total' => 1, 'offset' => 0, 'limit' => 10], $response['paging']);
        $this->assertEquals(1, $response['results'][0]['collection']['id']);
    }

    /**
     * @test
     * @vcr guzzle_it_can_create_preference
     */
    public function itCanCreatePreference()
    {
        $mp = new MercadoPago(new GuzzleClient());
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
     * @vcr guzzle_it_can_create_preference_error
     */
    public function itCanCreatePreferenceError()
    {
        $mp = new MercadoPago(new GuzzleClient());
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
     * @vcr guzzle_it_can_get_preference
     */
    public function itCanGetPreferenceError()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $preference = $mp->getPreference('SOME_ID');

        $this->assertEquals('SOME_ID', $preference['id']);
        $this->assertEquals('foo', $preference['items'][0]['title']);
    }

    /** @test **/
    public function itCanUseSandboxMode()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [
                    '/sandbox/collections/search',
                    ['limit' => 10, 'offset' => 0],
                    ['access_token' => 'SOME_ACCESS_TOKEN'],
                ],
                [
                    '/collections/search',
                    ['limit' => 10, 'offset' => 0],
                    ['access_token' => 'SOME_ACCESS_TOKEN'],
                ]
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $mp->enableSandboxMode();
        $response = $mp->searchPayments();

        $this->assertEquals([], $response);

        $mp->disableSandboxMode();
        $response = $mp->searchPayments();

        $this->assertEquals([], $response);
    }

    /**
     * @test
     * @vcr guzzle_it_can_create_preapproval_payment
     * @expectedException \MercadoPago\Exceptions\MercadoPagoException
     * @expectedExceptionMessage Cannot operate between different countries
     */
    public function itCanCreatePreapprovalPayment()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $mp->createPreapprovalPayment([
            "payer_email" => "foo@bar.com",
            "back_url" => "http://www.my-site.com",
            "reason" => "Monthly subscription to premium package",
            "external_reference" => "OP-1234",
            "auto_recurring" => [
                "frequency" => 0,
                "frequency_type" => "months",
                "transaction_amount" => 60,
                "currency_id" => "USD",
                "start_date" => "2014-12-10T14:58:11.778-03:00",
                "end_date" => "2015-06-10T14:58:11.778-03:00"
            ]
        ]);
    }

    /**
     * @test
     * @vcr guzzle_it_can_get_preapproval_payment
     * @expectedException \MercadoPago\Exceptions\MercadoPagoException
     * @expectedExceptionMessage The preapproval with id 123 does not exist
     */
    public function itCanGetPreapprovalPayment()
    {
        $mp = new MercadoPago(new GuzzleClient());
        $mp->setAccessToken('SOME_ACCESS_TOKEN');
        $mp->getPreapprovalPayment('123');
    }

    /** @test **/
    public function itCanGetAuthorizedPayment()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('get')
            ->with(
                '/authorized_payments/123',
                [],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->getAuthorizedPayment(123);

        $this->assertEquals([], $response);
    }

    /** @test **/
    public function itCanRefundPayment()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/collections/123',
                ['status' => 'refunded'],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->refundPayment(123);

        $this->assertEquals([], $response);
    }

    /** @test **/
    public function itCanCancelPayment()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/collections/123',
                ['status' => 'cancelled'],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->cancelPayment(123);

        $this->assertEquals([], $response);
    }

    /** @test **/
    public function itCanCancelPreapprovalPayment()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/preapproval/123',
                ['status' => 'cancelled'],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->cancelPreapprovalPayment(123);

        $this->assertEquals([], $response);
    }

    /** @test **/
    public function itCanUpdatePreference()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/checkout/preferences/123',
                ['data' => 'foo'],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->updatePreference(123, [
            'data' => 'foo',
        ]);

        $this->assertEquals([], $response);
    }

    /** @test **/
    public function itCanUpdatePreapprovalPayment()
    {
        $client = $this->getMockBuilder(GuzzleClient::class)->getMock();

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/preapproval/123',
                ['data' => 'foo'],
                ['access_token' => 'SOME_ACCESS_TOKEN']
            )
            ->will($this->returnValue(new Response(200, '{}')));

        $mp = new MercadoPago($client);
        $mp->setAccessToken('SOME_ACCESS_TOKEN');

        $response = $mp->updatePreapprovalPayment(123, [
            'data' => 'foo',
        ]);

        $this->assertEquals([], $response);
    }
}
