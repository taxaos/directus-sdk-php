<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\SDK\Exception\UnauthorizedRequestException;
use Directus\SDK\Response\Entry;
use Directus\SDK\Response\EntryCollection;
use Directus\Util\ArrayUtils;
use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\Exception\ClientException;

/**
 * Abstract Base Client Remote
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
abstract class BaseClientRemote extends AbstractClient
{
    /**
     * Directus base url
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Directus hosted base url format
     *
     * @var string
     */
    protected $hostedBaseUrlFormat = 'https://%s.directus.io';

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

    const ACTIVITY_GET_ENDPOINT = 'activity';

    const BOOKMARKS_CREATE_ENDPOINT = 'bookmarks';
    const BOOKMARKS_READ_ENDPOINT = 'bookmarks/%s';
    const BOOKMARKS_DELETE_ENDPOINT = 'bookmarks/%s';
    const BOOKMARKS_ALL_ENDPOINT = 'bookmarks';
    const BOOKMARKS_USER_ENDPOINT = 'bookmarks/user/%s';

    const TABLE_ENTRIES_ENDPOINT = 'tables/%s/rows';
    const TABLE_ENTRY_ENDPOINT = 'tables/%s/rows/%s';
    const TABLE_ENTRY_CREATE_ENDPOINT = 'tables/%s/rows';
    const TABLE_ENTRY_UPDATE_ENDPOINT = 'tables/%s/rows/%s';
    const TABLE_ENTRY_DELETE_ENDPOINT = 'tables/%s/rows/%s';
    const TABLE_LIST_ENDPOINT = 'tables';
    const TABLE_INFORMATION_ENDPOINT = 'tables/%s';
    const TABLE_PREFERENCES_ENDPOINT = 'tables/%s/preferences';
    const TABLE_CREATE_ENDPOINT = 'privileges/1'; // ID not being used but required @TODO: REMOVE IT
    const TABLE_DELETE_ENDPOINT = 'tables/%s';

    const COLUMN_LIST_ENDPOINT = 'tables/%s/columns';
    const COLUMN_CREATE_ENDPOINT = 'tables/%s/columns';
    const COLUMN_DELETE_ENDPOINT = 'tables/%s/columns/%s';
    const COLUMN_INFORMATION_ENDPOINT = 'tables/%s/columns/%s';
    const COLUMN_OPTIONS_CREATE_ENDPOINT = 'tables/%s/columns/%s/%s';

    const GROUP_LIST_ENDPOINT = 'groups';
    const GROUP_CREATE_ENDPOINT = 'groups';
    const GROUP_INFORMATION_ENDPOINT = 'groups/%s';
    const GROUP_PRIVILEGES_ENDPOINT = 'privileges/%s';
    const GROUP_PRIVILEGES_CREATE_ENDPOINT = 'privileges/%s';

    const FILE_LIST_ENDPOINT = 'files';
    const FILE_CREATE_ENDPOINT = 'files';
    const FILE_UPDATE_ENDPOINT = 'files/%s';
    const FILE_INFORMATION_ENDPOINT = 'files/%s';

    const SETTING_LIST_ENDPOINT = 'settings';
    const SETTING_COLLECTION_GET_ENDPOINT = 'settings/%s';
    const SETTING_COLLECTION_UPDATE_ENDPOINT = 'settings/%s';

    const MESSAGES_CREATE_ENDPOINT = 'messages/rows';
    const MESSAGES_LIST_ENDPOINT = 'messages/rows';
    const MESSAGES_GET_ENDPOINT = 'messages/rows/%s';
    const MESSAGES_USER_LIST_ENDPOINT = 'messages/user/%s';

    public function __construct($accessToken, $options = [])
    {
        $this->accessToken = $accessToken;

        if (isset($options['base_url'])) {
            $this->baseUrl = rtrim($options['base_url'], '/');
            $this->baseEndpoint = $this->baseUrl . '/api';
        }

        $instanceKey = isset($options['instance_key']) ? $options['instance_key'] : false;
        if ($instanceKey) {
            $this->instanceKey = $instanceKey;
            $this->baseUrl = sprintf($this->hostedBaseUrlFormat, $instanceKey);
            $this->baseEndpoint = $this->baseUrl . '/api';
        }

        $this->apiVersion = isset($options['version']) ? $options['version'] : 1;
        $this->baseEndpoint .= '/' . $this->getAPIVersion();

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
     * Get the base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
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
        return new HTTPClient(array('base_url' => rtrim($this->baseEndpoint, '/') . '/'));
    }

    /**
     * Perform a HTTP Request
     *
     * @param $method
     * @param $path
     * @param array $params
     *
     * @return Entry|EntryCollection
     *
     * @throws UnauthorizedRequestException
     */
    public function performRequest($method, $path, array $params = [])
    {
        $request = $this->buildRequest($method, $path, $params);

        try {
            $response = $this->httpClient->send($request);
            $content = json_decode($response->getBody()->getContents(), true);
            return $this->createResponseFromData($content);
        } catch (ClientException $ex) {
            if ($ex->getResponse()->getStatusCode() == 401) {
                $message = sprintf('Unauthorized %s Request to %s', $request->getMethod(), $request->getUrl());
                throw new UnauthorizedRequestException($message);
            }

            throw $ex;
        }
    }

    /**
     * Build a request object
     *
     * @param $method
     * @param $path
     * @param $params
     *
     * @return \GuzzleHttp\Message\Request
     */
    public function buildRequest($method, $path, array $params = [])
    {
        $body = ArrayUtils::get($params, 'body', []);
        $query = ArrayUtils::get($params, 'query', []);

        $options = [
            'auth' => [$this->accessToken, '']
        ];

        if (in_array($method, ['POST', 'PUT']) && $body) {
            $options['body'] = $body;
        }

        $request = $this->httpClient->createRequest($method, $path, $options);

        if ($query) {
            $q = $request->getQuery();
            foreach($query as $key => $value) {
                $q->set($key, $value);
            }
        }

        return $request;
    }

    /**
     * Build a endpoint path based on a format
     *
     * @param string $pathFormat
     * @param string|array $variables
     *
     * @return string
     */
    public function buildPath($pathFormat, $variables = [])
    {
        return vsprintf(ltrim($pathFormat, '/'), $variables);
    }
}
