<?php

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Directus\SDK\Client
     */
    protected $client;
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public function setUp()
    {
        parent::setUp();

        $this->client = new \Directus\SDK\Client('token');
        $this->httpClient = $this->client->getHTTPClient();
    }

    public function testClient()
    {
        $client = $this->client;
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getDefaultHTTPClient());
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getHTTPClient());
        $this->assertSame('token', $client->getAccessToken());

        $client->setAccessToken('newToken');
        $this->assertSame('newToken', $client->getAccessToken());

        $this->assertEquals(1, $client->getAPIVersion());
        $this->assertNull($client->getInstanceKey());
    }

    public function testOptions()
    {
        $client = new \Directus\SDK\Client('token', [
            'base_url' => 'http://directus.local'
        ]);

        $this->assertSame('http://directus.local/1/', $client->getBaseEndpoint());

        $client = new \Directus\SDK\Client('token', [
            'base_url' => 'http://directus.local',
            'version' => 2
        ]);

        $this->assertSame('http://directus.local/2/', $client->getBaseEndpoint());

        $this->assertEquals(2, $client->getAPIVersion());
    }

    public function testHostedClient()
    {
        $instanceKey = 'account--instance';
        $client = new \Directus\SDK\Client('token', ['instance_key' => $instanceKey]);

        $expectedEndpoint = 'https://'.$instanceKey.'.directus.io/api/1/';
        $this->assertSame($expectedEndpoint, $client->getBaseEndpoint());

        $client = new \Directus\SDK\Client('token', [
            'base_url' => 'http://directus.local',
            'instance_key' => $instanceKey
        ]);

        $this->assertSame($expectedEndpoint, $client->getBaseEndpoint());
        $this->assertEquals($instanceKey, $client->getInstanceKey());
    }

    public function testRequest()
    {
        $client = $this->client;
        $request = $this->client->buildRequest('GET', $client::TABLE_ENTRIES_ENDPOINT, 'articles');
        $this->assertInstanceOf('\GuzzleHttp\Message\Request', $request);
    }

    public function testEndpoints()
    {
        $client =  $this->client;

        $endpoint = $this->client->buildPath($client::TABLE_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'tables');

        $endpoint = $this->client->buildPath($client::TABLE_INFORMATION_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRIES_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles/rows');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRIES_ENDPOINT, ['articles']);
        $this->assertSame($endpoint, 'tables/articles/rows');

        $endpoint = $this->client->buildPath($client::TABLE_ENTRY_ENDPOINT, ['articles', 1]);
        $this->assertSame($endpoint, 'tables/articles/rows/1');

        $endpoint = $this->client->buildPath($client::TABLE_PREFERENCES_ENDPOINT, 'articles');
        $this->assertSame($endpoint, 'tables/articles/preferences');

        $endpoint = $this->client->buildPath($client::COLUMN_LIST_ENDPOINT, ['articles']);
        $this->assertSame($endpoint, 'tables/articles/columns');

        $endpoint = $this->client->buildPath($client::COLUMN_INFORMATION_ENDPOINT, ['articles', 'body']);
        $this->assertSame($endpoint, 'tables/articles/columns/body');

        $endpoint = $this->client->buildPath($client::GROUP_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'groups');

        $endpoint = $this->client->buildPath($client::GROUP_INFORMATION_ENDPOINT, 1);
        $this->assertSame($endpoint, 'groups/1');

        $endpoint = $this->client->buildPath($client::GROUP_PRIVILEGES_ENDPOINT, 1);
        $this->assertSame($endpoint, 'privileges/1');

        $endpoint = $this->client->buildPath($client::FILE_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'files');

        $endpoint = $this->client->buildPath($client::FILE_INFORMATION_ENDPOINT, 1);
        $this->assertSame($endpoint, 'files/1');

        $endpoint = $this->client->buildPath($client::SETTING_LIST_ENDPOINT);
        $this->assertSame($endpoint, 'settings');

        $endpoint = $this->client->buildPath($client::SETTING_COLLECTION_ENDPOINT, 'global');
        $this->assertSame($endpoint, 'settings/global');
    }

    public function testFetchTables()
    {
        $this->mockResponse('fetchTables.txt');
        $response = $this->client->fetchTables();
        $this->assertInternalType('array', $response);

        $this->mockResponse('fetchTablesEmpty.txt');
        $response = $this->client->fetchTables();
        $this->assertInternalType('array', $response);
    }

    public function testFetchTableInformation()
    {
        $this->mockResponse('fetchTableInformation.txt');
        $response = $this->client->fetchTableInfo('articles');
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchTableInformationEmpty.txt');
        $response = $this->client->fetchTableInfo('articles');
        $this->assertFalse($response);
    }

    public function testFetchTablePreferences()
    {
        $this->mockResponse('fetchTablePreferences.txt');
        $response = $this->client->fetchTableInfo('articles');
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchTablePreferencesEmpty.txt');
        $response = $this->client->fetchTableInfo('articles');
        $this->assertFalse($response);
    }

    public function testFetchItems()
    {
        $this->mockResponse('fetchItems.txt');
        $response = $this->client->fetchItems('articles');

        $this->assertObjectHasAttribute('Active', $response);
        $this->assertObjectHasAttribute('Draft', $response);
        $this->assertObjectHasAttribute('Delete', $response);
        $this->assertObjectHasAttribute('rows', $response);
        $this->assertInternalType('array', $response->rows);

        $this->mockResponse('fetchItemsEmpty.txt');
        $response = $this->client->fetchItems('articles');

        $this->assertObjectHasAttribute('Active', $response);
        $this->assertObjectHasAttribute('Draft', $response);
        $this->assertObjectHasAttribute('Delete', $response);
        $this->assertObjectHasAttribute('rows', $response);
        $this->assertInternalType('array', $response->rows);
    }

    public function testFetchItem()
    {
        $this->mockResponse('fetchItem.txt');
        $response = $this->client->fetchItem('articles', 1);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchItemEmpty.txt');
        $response = $this->client->fetchItem('articles', 3);
        $this->assertNull($response);
    }

    public function testFetchColumns()
    {
        $this->mockResponse('fetchColumns.txt');
        $response = $this->client->fetchColumns('articles');
        $this->assertInternalType('array', $response);

        $this->mockResponse('fetchColumnsEmpty.txt');
        $response = $this->client->fetchColumns('articles');
        $this->assertInternalType('array', $response);
    }

    public function testFetchColumnInformation()
    {
        $this->mockResponse('fetchColumnInfo.txt');
        $response = $this->client->fetchColumnInfo('articles', 'title');
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchColumnInfoEmpty.txt');
        $response = $this->client->fetchColumnInfo('articles', 'name');
        $this->assertInternalType('object', $response);
        $this->assertObjectHasAttribute('message', $response);
    }

    public function testFetchGroups()
    {
        $this->mockResponse('fetchGroups.txt');
        $response = $this->client->fetchGroups();
        $this->assertInternalType('object', $response);
        $this->assertObjectHasAttribute('total', $response);
        $this->assertObjectHasAttribute('rows', $response);

        $this->mockResponse('fetchGroupsEmpty.txt');
        $response = $this->client->fetchGroups();
        $this->assertInternalType('object', $response);
        $this->assertObjectHasAttribute('total', $response);
        $this->assertObjectHasAttribute('rows', $response);
    }

    public function testFetchGroupInformation()
    {
        $this->mockResponse('fetchGroupInfo.txt');
        $response = $this->client->fetchGroupInfo(1);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchGroupInfoEmpty.txt');
        $response = $this->client->fetchGroupInfo(2);
        $this->assertFalse($response);
    }

    public function testFetchGroupPrivileges()
    {
        $this->mockResponse('fetchGroupPrivileges.txt');
        $response = $this->client->fetchGroupPrivileges(1);
        $this->assertInternalType('array', $response);
        $this->assertInternalType('object', $response[0]);
        $this->assertObjectHasAttribute('allow_view', $response[0]);

        $this->mockResponse('fetchGroupPrivilegesEmpty.txt');
        $response = $this->client->fetchGroupPrivileges(30);
        $this->assertInternalType('array', $response);
        $this->assertInternalType('object', $response[0]);
        $this->assertObjectNotHasAttribute('allow_view', $response[0]);
    }

    public function testFetchFiles()
    {
        $this->mockResponse('fetchFiles.txt');
        $response = $this->client->fetchFiles();
        $this->assertInternalType('object', $response);
        $this->assertObjectHasAttribute('rows', $response);
        $this->assertInternalType('array', $response->rows);

        $this->mockResponse('fetchFilesEmpty.txt');
        $response = $this->client->fetchFiles();
        $this->assertInternalType('object', $response);
        $this->assertObjectHasAttribute('rows', $response);
        $this->assertInternalType('array', $response->rows);
    }

    public function testFetchFileInformation()
    {
        $this->mockResponse('fetchFileInformation.txt');
        $response = $this->client->fetchFileInfo(1);
        $this->assertInternalType('object', $response);

        $this->mockResponse('fetchFileInformationEmpty.txt');
        $response = $this->client->fetchFileInfo(2);
        $this->assertNull($response);
    }

    public function testFetchSettings()
    {
        $this->mockResponse('fetchSettings.txt');
        $response = $this->client->fetchSettings();
        $this->assertInternalType('object', $response);
    }

    public function testFetchSettingCollection()
    {
        $this->mockResponse('fetchSettingsCollection.txt');
        $response = $this->client->fetchSettingCollection('global');
        $this->assertInternalType('object', $response);
    }

    protected function mockResponse($path)
    {
        static $mock = null;
        if ($mock === null) {
            $mock = new \GuzzleHttp\Subscriber\Mock();
        }

        $mockPath = __DIR__.'/Mock/'.$path;
        $mockContent = file_get_contents($mockPath);
        $mock->addResponse($mockContent);

        $this->httpClient->getEmitter()->attach($mock);
    }
}
