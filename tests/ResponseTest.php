<?php

namespace Tests;

use MercadoPago\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /** @test **/
    public function itCanCreateInstance()
    {
        new Response(1, '');
    }

    /** @test **/
    public function itCanCreateSuccessResponse()
    {
        $response = new Response(200, '{"message": "foo"}');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'foo'], $response->getData());
        $this->assertFalse($response->isError());
        $this->assertNull($response->getError());
    }

    /** @test **/
    public function itCanCreateErrorResponse()
    {
        $response = new Response(500, '{"message": "bar"}');

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['message' => 'bar'], $response->getData());
        $this->assertTrue($response->isError());
    }

    /** @test **/
    public function itCanCreateGetNestedData()
    {
        $response = new Response(500, '{"message": "foo", "data": {"name": "John"}}');

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['message' => 'foo', 'data' => ['name' => 'John']], $response->getData());
        $this->assertTrue($response->isError());
        $this->assertEquals('foo', $response->get('message'));
        $this->assertEquals('John', $response->get('data.name'));
        $this->assertNull($response->get('not_found'));
    }

    /** @test **/
    public function itCanGetCauses()
    {
        $response = new Response(
            401,
            '{"message": "Error message", "cause": {"code": 4000, "description": "Error Description"}}'
        );
        $this->assertTrue($response->isError());
        $this->assertEquals('Error message - 4000: Error Description', $response->getError());
    }

    /** @test **/
    public function itCanGetNestedCauses()
    {
        $response = new Response(
            401,
            json_encode([
                'message' => 'Error message',
                'cause' => [
                    ['code' => 4000, 'description' => 'Error Description'],
                    ['code' => 4001, 'description' => 'Another Error Description'],
                ]
            ])
        );
        $this->assertTrue($response->isError());
        $this->assertEquals(
            'Error message - 4000: Error Description - 4001: Another Error Description',
            $response->getError()
        );
    }

    /** @test **/
    public function itCanGetEmptyNestedCauses()
    {
        $response = new Response(
            401,
            '{"message": "Error message", "cause": []}'
        );
        $this->assertTrue($response->isError());
        $this->assertEquals('Error message', $response->getError());
    }
}
