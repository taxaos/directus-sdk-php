<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;
use Directus\Util\ArrayUtils;

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
        return $this->performRequest('GET', static::TABLE_ENTRIES_ENDPOINT, $tableName, null, $options);
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
        return $this->performRequest('PUT', static::TABLE_ENTRY_UPDATE_ENDPOINT, [$tableName, $id], $data);
    }

    public function deleteEntry($tableName, $ids)
    {
        return $this->performRequest('DELETE', static::TABLE_ENTRY_DELETE_ENDPOINT, [$tableName, $ids]);
    }

    public function createUser(array $data)
    {
        // @TODO: Add hooks
        if (ArrayUtils::has($data, 'password')) {
            // @NOTE: Use Directus password hash
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        return $this->createEntry('directus_users', $data);
    }

    public function updateUser($id, array $data)
    {
        // @TODO: Add hooks
        if (ArrayUtils::has($data, 'password')) {
            // @NOTE: Use Directus password hash
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        return $this->updateEntry('directus_users', $id, $data);
    }

    public function deleteUser($ids)
    {
        return $this->deleteEntry('directus_users', $ids);
    }

    public function createFile(array $data)
    {
        return $this->createEntry('directus_files', $data);
    }

    public function updateFile($id, array $data)
    {
        return $this->updateEntry('directus_files', $id, $data);
    }

    public function deleteFile($ids)
    {
        return $this->deleteEntry('directus_files', $ids);
    }
}
