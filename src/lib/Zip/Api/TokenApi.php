<?php
/**
 * TokenApi
 *
 * @package Zip
 * @author  Zip Co - Plugin Team
 */

namespace Zip\Api;

use Zip\ApiClient;
use Zip\ApiException;
use Zip\Configuration;
use Zip\ObjectSerializer;

class TokenApi
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
     * @return TokenApi
     */
    public function setApiClient(Zip\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation tokensCreate
     *
     * Create token
     *
     * @param  Zip\Model\CreateTokenRequest $body            (optional)
     * @param  string                       $idempotency_key The unique idempotency key. (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\Token
     */
    public function tokensCreate($body = null, $idempotency_key = null)
    {
        list($response) = $this->tokensCreateWithHttpInfo($body, $idempotency_key);
        return $response;
    }

    /**
     * Operation tokensCreateWithHttpInfo
     *
     * Create token
     *
     * @param  Zip\Model\CreateTokenRequest $body            (optional)
     * @param  string                       $idempotency_key The unique idempotency key. (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\Token, HTTP status code, HTTP response headers (array of strings)
     */
    public function tokensCreateWithHttpInfo($body = null, $idempotency_key = null)
    {
        // parse inputs
        $resourcePath = "/tokens";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/javascript'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/json'));

        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = $this->apiClient->getSerializer()->toHeaderValue($idempotency_key);
        }

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
                'Zip\Model\Token',
                '/tokens'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, 'Zip\Model\Token', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
            case 201:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'Zip\Model\Token', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            case 401:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            case 402:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            case 403:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            case 409:
                $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'Zip\Model\ErrorResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }

            throw $e;
        }
    }
}
