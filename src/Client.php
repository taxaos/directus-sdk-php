<?php

namespace Directus\SDK;

class Client extends BaseClient implements RequestsInterface
{
    protected $baseEndpoint = 'http://localhost/api';
    protected $hostedBaseEndpointFormat = 'https://%s.directus.io/api';

    public function fetchTables()
    {
        return $this->performRequest('GET', static::TABLE_LIST_ENDPOINT);
    }

    public function fetchTableInfo($tableName)
    {
        return $this->performRequest('GET', static::TABLE_INFORMATION_ENDPOINT, $tableName);
    }

    public function fetchColumns($tableName)
    {
        return $this->performRequest('GET', static::COLUMN_LIST_ENDPOINT, $tableName);
    }

    public function fetchColumnInfo($tableName, $columnName)
    {
        return $this->performRequest('GET', static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);
    }

    public function fetchItems($tableName)
    {
        return $this->performRequest('GET', static::TABLE_ENTRIES_ENDPOINT, $tableName);
    }

    public function fetchItem($tableName, $itemID)
    {
        return $this->performRequest('GET', static::TABLE_ENTRY_ENDPOINT, [$tableName, $itemID]);
    }

    public function fetchGroups()
    {
        return $this->performRequest('GET', static::GROUP_LIST_ENDPOINT);
    }

    public function fetchGroupInfo($groupID)
    {
        return $this->performRequest('GET', static::GROUP_INFORMATION_ENDPOINT, $groupID);
    }

    public function fetchGroupPrivileges($groupID)
    {
        return $this->performRequest('GET', static::GROUP_PRIVILEGES_ENDPOINT, $groupID);
    }

    public function fetchFiles()
    {
        return $this->performRequest('GET', static::FILE_LIST_ENDPOINT);
    }

    public function fetchFileInfo($fileID)
    {
        return $this->performRequest('GET', static::FILE_INFORMATION_ENDPOINT, $fileID);
    }

    public function fetchSettings()
    {
        return $this->performRequest('GET', static::SETTING_LIST_ENDPOINT);
    }

    public function fetchSettingCollection($collectionName)
    {
        return $this->performRequest('GET', static::SETTING_COLLECTION_ENDPOINT, $collectionName);
    }
}
