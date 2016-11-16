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
class ClientRemote extends BaseClientRemote
{
    protected $baseEndpoint = 'http://localhost/api';
    protected $hostedBaseEndpointFormat = 'https://%s.directus.io/api';

    public function getTables(array $params = [])
    {
        return $this->performRequest('GET', static::TABLE_LIST_ENDPOINT);
    }

    public function getTable($tableName)
    {
        return $this->performRequest('GET', static::TABLE_INFORMATION_ENDPOINT, $tableName);
    }

    public function getColumns($tableName, array $params = [])
    {
        return $this->performRequest('GET', static::COLUMN_LIST_ENDPOINT, $tableName);
    }

    public function getColumn($tableName, $columnName)
    {
        return $this->performRequest('GET', static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);
    }

    public function getEntries($tableName, array $options = [])
    {
        return $this->performRequest('GET', static::TABLE_ENTRIES_ENDPOINT, $tableName);
    }

    public function getEntry($tableName, $id, array $options = [])
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

    public function getGroups()
    {
        return $this->performRequest('GET', static::GROUP_LIST_ENDPOINT);
    }

    public function getGroup($groupID)
    {
        return $this->performRequest('GET', static::GROUP_INFORMATION_ENDPOINT, $groupID);
    }

    public function getGroupPrivileges($groupID)
    {
        return $this->performRequest('GET', static::GROUP_PRIVILEGES_ENDPOINT, $groupID);
    }

    public function getFiles()
    {
        return $this->performRequest('GET', static::FILE_LIST_ENDPOINT);
    }

    public function getFile($fileID)
    {
        return $this->performRequest('GET', static::FILE_INFORMATION_ENDPOINT, $fileID);
    }

    public function getSettings()
    {
        return $this->performRequest('GET', static::SETTING_LIST_ENDPOINT);
    }

    public function getSettingsByCollection($collectionName)
    {
        return $this->performRequest('GET', static::SETTING_COLLECTION_ENDPOINT, $collectionName);
    }

    public function getMessages($userId)
    {
        return $this->performRequest('GET', static::MESSAGES_USER_ENDPOINT, $userId);
    }

    public function createEntry($tableName, array $data)
    {
        return $this->performRequest('POST', static::TABLE_ENTRY_CREATE_ENDPOINT, $tableName, $data);
    }

    public function updateEntry($tableName, $id, array $data)
    {
        // TODO: Implement updateEntry() method.
    }

    public function deleteEntry($tableName, $ids)
    {
        // TODO: Implement deleteEntry() method.
    }

    public function createUser(array $data)
    {
        // TODO: Implement createUser() method.
    }

    public function updateUser($id, array $data)
    {
        // TODO: Implement updateUser() method.
    }

    public function deleteUser($ids)
    {
        // TODO: Implement deleteUser() method.
    }

    public function createFile(array $data)
    {
        // TODO: Implement createFile() method.
    }

    public function updateFile($id, array $data)
    {
        // TODO: Implement updateFile() method.
    }

    public function deleteFile($ids)
    {
        // TODO: Implement deleteFile() method.
    }
}
