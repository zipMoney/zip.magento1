<?php
/**
 * CustomerApi
 *
 * @package Zip
 * @author  Zip Co - Plugin Team
 */

namespace Zip\Api;

use Zip\ApiClient;
use Zip\ApiException;
use Zip\Configuration;
use Zip\ObjectSerializer;

class CustomerApi
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
     * @return CustomerApi
     */
    public function setApiClient(Zip\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation customersGet
     *
     * Retrieve customer
     *
     * @param  string $id (required)
     * @throws Zip\ApiException on non-2xx response
     * @return void
     */
    public function customersGet($id)
    {
        list($response) = $this->customersGetWithHttpInfo($id);
        return $response;
    }

    /**
     * Operation customersGetWithHttpInfo
     *
     * Retrieve customer
     *
     * @param  string $id (required)
     * @throws Zip\ApiException on non-2xx response
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function customersGetWithHttpInfo($id)
    {
        // verify the required parameter 'id' is set
        if ($id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling customersGet');
        }

        // parse inputs
        $resourcePath = "/customers/{id}";
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

        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                null,
                '/customers/{id}'
            );

            return array(null, $statusCode, $httpHeader);
        } catch (ApiException $e) {
            throw $e;
        }
    }

    /**
     * Operation customersList
     *
     * List customers
     *
     * @throws Zip\ApiException on non-2xx response
     * @return void
     */
    public function customersList()
    {
        list($response) = $this->customersListWithHttpInfo();
        return $response;
    }

    /**
     * Operation customersListWithHttpInfo
     *
     * List customers
     *
     * @throws Zip\ApiException on non-2xx response
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function customersListWithHttpInfo()
    {
        // parse inputs
        $resourcePath = "/customers";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/javascript'));
        if ($_header_accept !== null) {
            $headerParams['Accept'] = $_header_accept;
        }

        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array('application/javascript'));

        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }

        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                null,
                '/customers'
            );

            return array(null, $statusCode, $httpHeader);
        } catch (ApiException $e) {
            throw $e;
        }
    }
}