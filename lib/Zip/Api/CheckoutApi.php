<?php
/**
 * CheckoutApi
 *
 * @package Zip
 * @author  Zip Co - Plugin Team
 */

namespace Zip\Api;

use Zip\ApiClient;
use Zip\ApiException;
use Zip\Configuration;
use Zip\ObjectSerializer;

class CheckoutApi
{
    /**
     * API Client
     *
     * @var Zip\ApiClient instance of the ApiClient
     */
    protected $apiClient;

    /**
     * Constructor
     *
     * @param Zip\ApiClient|null $apiClient The api client to use
     */
    public function __construct(Zip\ApiClient $apiClient = null)
    {
        if ($apiClient === null) {
            $apiClient = new ApiClient();
        }

        $this->apiClient = $apiClient;
    }

    /**
     * Get API client
     *
     * @return Zip\ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Set the API client
     *
     * @param Zip\ApiClient $apiClient set the API client
     *
     * @return CheckoutApi
     */
    public function setApiClient(Zip\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation checkoutsCreate
     *
     * Create a checkout
     *
     * @param  Zip\Model\CreateCheckoutRequest $body (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\Checkout
     */
    public function checkoutsCreate($body = null)
    {
        list($response) = $this->checkoutsCreateWithHttpInfo($body);
        return $response;
    }

    /**
     * Operation checkoutsCreateWithHttpInfo
     *
     * Create a checkout
     *
     * @param  Zip\Model\CreateCheckoutRequest $body (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\Checkout, HTTP status code, HTTP response headers (array of strings)
     */
    public function checkoutsCreateWithHttpInfo($body = null)
    {
        // parse inputs
        $resourcePath = "/checkouts";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if ($_header_accept !== null) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/json'));

        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        // body params
        $_tempBody = null;
        if (isset($body)) {
            $_tempBody = $body;
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }

        // this endpoint requires API key authentication
        $apiKey = $this->apiClient->getApiKeyWithPrefix('Authorization');
        if (strlen($apiKey) !== 0) {
            $headerParams['Authorization'] = $apiKey;
        }

        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'POST',
                $queryParams,
                $httpBody,
                $headerParams,
                'Zip\Model\Checkout',
                '/checkouts'
            );

            return array($this->apiClient->getSerializer()
                ->deserialize($response, 'Zip\Model\Checkout', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\Checkout', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 402:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation checkoutsGet
     *
     * Retrieve a checkout
     *
     * @param  string $id (required)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\Checkout
     */
    public function checkoutsGet($id)
    {
        list($response) = $this->checkoutsGetWithHttpInfo($id);
        return $response;
    }

    /**
     * Operation checkoutsGetWithHttpInfo
     *
     * Retrieve a checkout
     *
     * @param  string $id (required)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\Checkout, HTTP status code, HTTP response headers (array of strings)
     */
    public function checkoutsGetWithHttpInfo($id)
    {
        // verify the required parameter 'id' is set
        if ($id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling checkoutsGet');
        }

        // parse inputs
        $resourcePath = "/checkouts/{id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if ($_header_accept !== null) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/json'));

        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{id}',
                $this->apiClient->getSerializer()->toPathValue($id),
                $resourcePath
            );
        }

        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }

        // this endpoint requires API key authentication
        $apiKey = $this->apiClient->getApiKeyWithPrefix('Authorization');
        if (strlen($apiKey) !== 0) {
            $headerParams['Authorization'] = $apiKey;
        }

        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                'Zip\Model\Checkout',
                '/checkouts/{id}'
            );

            return array($this->apiClient->getSerializer()
                ->deserialize($response, 'Zip\Model\Checkout', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\Checkout', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 409:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }
}
