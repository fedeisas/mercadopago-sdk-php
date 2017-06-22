<?php

namespace MercadoPago;

use MercadoPago\Exceptions\MercadoPagoException;
use MercadoPago\Http\Client;
use MercadoPago\Http\Response;
use RuntimeException;

class MercadoPago
{
    const VERSION = '0.5.2';

    /**
     * @var string|null
     */
    protected $clientId = null;

    /**
     * @var string|null
     */
    protected $clientSecret = null;

    /**
     * @var string|null
     */
    protected $accessToken = null;

    /**
     * @var bool
     */
    protected $sandbox = false;

    /**
     * @var Client
     */
    protected $client;

    /**
     * MercadoPago constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get Http Client.
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Enable sandbox mode.
     * @return void
     */
    public function enableSandboxMode()
    {
        $this->sandbox = true;
    }

    /**
     * Disable sandbox mode.
     * @return void
     */
    public function disableSandboxMode()
    {
        $this->sandbox = false;
    }

    /**
     * Get current access token. If not set, it will use credentials to request one.
     * @return string|null
     * @throws MercadoPagoException If server response is not successful.
     * @throws RuntimeException If Client ID or Client Secret are not set.
     */
    public function getAccessToken()
    {
        if (!is_null($this->accessToken)) {
            return $this->accessToken;
        } else {
            if (empty($this->clientId) || empty($this->clientSecret)) {
                throw new RuntimeException('Client ID and Client Secret are required to request a new access token.');
            }

            $data = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials'
            ];

            $response = $this->client->post(
                '/oauth/token',
                $data,
                ['form' => true]
            );

            if ($response->getStatusCode() !== 200) {
                throw new MercadoPagoException($response->get('message'), $response->getStatusCode());
            }

            $this->accessToken = $response->get('access_token');

            return $this->accessToken;
        }
    }

    /**
     * Set an access token.
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken = null)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param null $clientId
     * @param null $clientSecret
     */
    public function setCredentials($clientId = null, $clientSecret = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get information for specific authorized payment.
     * @param int $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function getAuthorizedPayment($id)
    {
        $response = $this->client->get(
            '/authorized_payments/' . $id,
            [],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Refund accredited payment.
     * @param int $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function refundPayment($id)
    {
        $response = $this->client->put(
            '/collections/' . $id,
            [
                'status' => 'refunded',
            ],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Cancel pending payment
     * @param int $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function cancelPayment($id)
    {
        $response = $this->client->put(
            '/collections/' . $id,
            [
                'status' => 'cancelled',
            ],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Cancel preapproval payment.
     * @param int $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function cancelPreapprovalPayment($id)
    {
        $response = $this->client->put(
            '/preapproval/' . $id,
            [
                'status' => 'cancelled',
            ],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Search payments according to filters, with pagination.
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function searchPayments(array $filters = [], $offset = 0, $limit = 0)
    {
        $response = $this->client->get(
            ($this->sandbox ? '/sandbox' : '') . '/collections/search',
            $filters + compact('limit', 'offset'),
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Create a checkout preference.
     * @param array $preference
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function createPreference($preference)
    {
        $response = $this->client->post(
            '/checkout/preferences',
            $preference,
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Update a checkout preference.
     * @param string $id
     * @param array $preference
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function updatePreference($id, $preference)
    {
        $response = $this->client->put(
            '/checkout/preferences/' . $id,
            $preference,
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Get a checkout preference.
     * @param string $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function getPreference($id)
    {
        $response = $this->client->get(
            '/checkout/preferences/' . $id,
            [],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Create a preapproval payment.
     * @param array $preapprovalPayment
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function createPreapprovalPayment($preapprovalPayment)
    {
        $response = $this->client->post(
            '/preapproval',
            $preapprovalPayment,
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Get a preapproval payment.
     * @param int $id
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function getPreapprovalPayment($id)
    {
        $response = $this->client->get(
            '/preapproval/' . $id,
            [],
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * Update a preapproval payment.
     * @param int $id
     * @param array $payment
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    public function updatePreapprovalPayment($id, array $payment = [])
    {
        $response = $this->client->put(
            '/preapproval/' . $id,
            $payment,
            ['access_token' => $this->getAccessToken()]
        );

        return $this->handleResponse($response);
    }

    /**
     * @param Response $response
     * @return array
     * @throws MercadoPagoException If request failed.
     */
    private function handleResponse(Response $response)
    {
        if ($response->isError()) {
            throw new MercadoPagoException($response->getError(), $response->getStatusCode());
        }

        return $response->getData();
    }
}
