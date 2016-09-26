<?php

namespace Directus\SDK;

use GuzzleHttp\Client as HTTPClient;

abstract class BaseClient
{
    /**
     * Directus Server base endpoint
     *
     * @var string
     */
    protected $baseEndpoint;

    /**
     * API Version
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * Directus Hosted endpoint format.
     *
     * @var string
     */
    protected $hostedBaseEndpointFormat;

    /**
     * Directus Hosted Instance Key
     *
     * @var int|string
     */
    protected $instanceKey;

    /**
     * Authentication Token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * HTTP Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * HTTP Client request timeout
     *
     * @var int
     */
    protected $timeout = 60;

    const TABLE_ENTRIES_ENDPOINT = 'tables/%s/rows';
    const TABLE_ENTRY_ENDPOINT = 'tables/%s/rows/%s';
    const TABLE_LIST_ENDPOINT = 'tables';
    const TABLE_INFORMATION_ENDPOINT = 'tables/%s';
    const TABLE_PREFERENCES_ENDPOINT = 'tables/%s/preferences';

    const COLUMN_LIST_ENDPOINT = 'tables/%s/columns';
    const COLUMN_INFORMATION_ENDPOINT = 'tables/%s/columns/%s';

    const GROUP_LIST_ENDPOINT = 'groups';
    const GROUP_INFORMATION_ENDPOINT = 'groups/%s';
    const GROUP_PRIVILEGES_ENDPOINT = 'privileges/%s';

    const FILE_LIST_ENDPOINT = 'files';
    const FILE_INFORMATION_ENDPOINT = 'files/%s';

    const SETTING_LIST_ENDPOINT = 'settings';
    const SETTING_COLLECTION_ENDPOINT = 'settings/%s';

    public function __construct($accessToken, $options = [])
    {
        $this->accessToken = $accessToken;

        if (isset($options['base_url'])) {
            $this->baseEndpoint = $options['base_url'];
        }

        $instanceKey = isset($options['instance_key']) ? $options['instance_key'] : false;
        if ($instanceKey) {
            $this->instanceKey = $instanceKey;
            $this->baseEndpoint = sprintf($this->hostedBaseEndpointFormat, $instanceKey);
        }

        $this->apiVersion = isset($options['version']) ? $options['version'] : 1;
        $this->baseEndpoint = rtrim(rtrim($this->baseEndpoint, '/').'/'.$this->apiVersion, '/').'/';

        $this->setHTTPClient($this->getDefaultHTTPClient());
    }

    /**
     * Get the base endpoint url
     *
     * @return string
     */
    public function getBaseEndpoint()
    {
        return $this->baseEndpoint;
    }

    /**
     * Get API Version
     *
     * @return int|string
     */
    public function getAPIVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Get the authentication access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set a new authentication access token
     *
     * @param $newAccessToken
     */
    public function setAccessToken($newAccessToken)
    {
        $this->accessToken = $newAccessToken;
    }

    /**
     * Get the Directus hosted instance key
     *
     * @return null|string
     */
    public function getInstanceKey()
    {
        return $this->instanceKey;
    }

    /**
     * Set the HTTP Client
     *
     * @param HTTPClient $httpClient
     */
    public function setHTTPClient(HTTPClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the HTTP Client
     *
     * @return HTTPClient|null
     */
    public function getHTTPClient()
    {
        return $this->httpClient;
    }

    /**
     * Get the default HTTP Client
     *
     * @return HTTPClient
     */
    public function getDefaultHTTPClient()
    {
        return new HTTPClient(array('base_url' => $this->baseEndpoint));
    }

    public function performRequest($method, $pathFormat, $variables = [])
    {
        $request = $this->buildRequest($method, $pathFormat, $variables);
        $response = $this->httpClient->send($request);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Build a request object
     *
     * @param $method
     * @param $pathFormat
     * @param $variables
     *
     * @return \GuzzleHttp\Message\Request
     */
    public function buildRequest($method, $pathFormat, $variables = [])
    {
        $request = $this->httpClient->createRequest($method, $this->buildPath($pathFormat, $variables), [
            'auth' => [$this->accessToken, '']
        ]);

        return $request;
    }

    /**
     * Build a endpoint path based on a format
     *
     * @param string $pathFormat
     * @param array $variables
     *
     * @return string
     */
    public function buildPath($pathFormat, $variables = [])
    {
        return vsprintf(ltrim($pathFormat, '/'), $variables);
    }
}
