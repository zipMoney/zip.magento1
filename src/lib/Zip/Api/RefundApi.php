<?php
/**
 * RefundApi
 *
 * @package Zip
 * @author  Zip Co - Plugin Team
 */

namespace Zip\Api;

use Zip\ApiClient;
use Zip\ApiException;
use Zip\Configuration;
use Zip\ObjectSerializer;

class RefundApi
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
     * @return RefundApi
     */
    public function setApiClient(Zip\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation refundsCreate
     *
     * Create a refund
     *
     * @param  Zip\Model\CreateRefundRequest $body            (optional)
     * @param  string                        $idempotency_key The unique idempotency key. (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\Refund
     */
    public function refundsCreate($body = null, $idempotency_key = null)
    {
        list($response) = $this->refundsCreateWithHttpInfo($body, $idempotency_key);
        return $response;
    }

    /**
     * Operation refundsCreateWithHttpInfo
     *
     * Create a refund
     *
     * @param  Zip\Model\CreateRefundRequest $body            (optional)
     * @param  string                        $idempotency_key The unique idempotency key. (optional)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\Refund, HTTP status code, HTTP response headers (array of strings)
     */
    public function refundsCreateWithHttpInfo($body = null, $idempotency_key = null)
    {
        // parse inputs
        $resourcePath = "/refunds";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/javascript'));
        if ($_header_accept !== null) {
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
                'Zip\Model\Refund',
                '/refunds'
            );

            return array($this->apiClient->getSerializer()
                ->deserialize($response, 'Zip\Model\Refund', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\Refund', $e->getResponseHeaders());
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
            }

            throw $e;
        }
    }

    /**
     * Operation refundsList
     *
     * List refunds
     *
     * @param  string $charge_id (optional)
     * @param  int    $skip      Number of items to skip when paging (optional, default to 0)
     * @param  int    $limit     Number of items to retrieve when paging (optional, default to 100)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\InlineResponse200[]
     */
    public function refundsList($charge_id = null, $skip = null, $limit = null)
    {
        list($response) = $this->refundsListWithHttpInfo($charge_id, $skip, $limit);
        return $response;
    }

    /**
     * Operation refundsListWithHttpInfo
     *
     * List refunds
     *
     * @param  string $charge_id (optional)
     * @param  int    $skip      Number of items to skip when paging (optional, default to 0)
     * @param  int    $limit     Number of items to retrieve when paging (optional, default to 100)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\InlineResponse200[], HTTP status code, HTTP response headers (array of strings)
     */
    public function refundsListWithHttpInfo($charge_id = null, $skip = null, $limit = null)
    {
        // parse inputs
        $resourcePath = "/refunds";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/javascript'));
        if ($_header_accept !== null) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/javascript'));

        // query params
        if ($charge_id !== null) {
            $queryParams['chargeId'] = $this->apiClient->getSerializer()->toQueryValue($charge_id);
        }

        // query params
        if ($skip !== null) {
            $queryParams['skip'] = $this->apiClient->getSerializer()->toQueryValue($skip);
        }

        // query params
        if ($limit !== null) {
            $queryParams['limit'] = $this->apiClient->getSerializer()->toQueryValue($limit);
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
                'Zip\Model\InlineResponse200[]',
                '/refunds'
            );

            return array($this->apiClient->getSerializer()
                ->deserialize($response, 'Zip\Model\InlineResponse200[]', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\InlineResponse200[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation refundsRetrieve
     *
     * Retrieve a refund
     *
     * @param  string $id The id of the refund (required)
     * @throws Zip\ApiException on non-2xx response
     * @return Zip\Model\Refund
     */
    public function refundsRetrieve($id)
    {
        list($response) = $this->refundsRetrieveWithHttpInfo($id);
        return $response;
    }

    /**
     * Operation refundsRetrieveWithHttpInfo
     *
     * Retrieve a refund
     *
     * @param  string $id The id of the refund (required)
     * @throws Zip\ApiException on non-2xx response
     * @return array of Zip\Model\Refund, HTTP status code, HTTP response headers (array of strings)
     */
    public function refundsRetrieveWithHttpInfo($id)
    {
        // verify the required parameter 'id' is set
        if ($id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling refundsRetrieve');
        }

        // parse inputs
        $resourcePath = "/refunds/{id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/javascript'));
        if ($_header_accept !== null) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/javascript'));

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
                'Zip\Model\Refund',
                '/refunds/{id}'
            );

            return array($this->apiClient->getSerializer()
                ->deserialize($response, 'Zip\Model\Refund', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()
                        ->deserialize($e->getResponseBody(), 'Zip\Model\Refund', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }
}
