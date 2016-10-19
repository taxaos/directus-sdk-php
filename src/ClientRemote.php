<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

/**
 * Client Remote
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientRemote extends BaseClientRemote implements RequestsInterface
{
    protected $baseEndpoint = 'http://localhost/api';
    protected $hostedBaseEndpointFormat = 'https://%s.directus.io/api';

    public function getTables(array $params = [])
    {
        return $this->performRequest('GET', static::TABLE_LIST_ENDPOINT);
    }

    public function fetchTableInfo($tableName)
    {
        return $this->performRequest('GET', static::TABLE_INFORMATION_ENDPOINT, $tableName);
    }

    public function getColumns(array $params = [])
    {
        throw new \Exception('Endpoint not defined yet');
    }

    public function getTableColumns($tableName, array $params = [])
    {
        return $this->performRequest('GET', static::COLUMN_LIST_ENDPOINT, $tableName);
    }

    public function fetchColumnInfo($tableName, $columnName)
    {
        return $this->performRequest('GET', static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);
    }

    public function getEntries($tableName, array $options = [])
    {
        return $this->performRequest('GET', static::TABLE_ENTRIES_ENDPOINT, $tableName);
    }

    public function getEntry($id, $tableName, array $options = [])
    {
        return $this->performRequest('GET', static::TABLE_ENTRY_ENDPOINT, [$tableName, $id]);
    }

    public function getUsers(array $params = [])
    {
        return $this->getEntries('directus_users', $params);
    }

    public function getUser($id, array $params = [])
    {
        return $this->getEntry($id, 'directus_users', $params);
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
