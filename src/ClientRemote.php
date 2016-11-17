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

    /**
     * @inheritdoc
     */
    public function getTables(array $params = [])
    {
        return $this->performRequest('GET', static::TABLE_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getTable($tableName)
    {
        return $this->performRequest('GET', static::TABLE_INFORMATION_ENDPOINT, $tableName);
    }

    /**
     * @inheritdoc
     */
    public function getColumns($tableName, array $params = [])
    {
        return $this->performRequest('GET', static::COLUMN_LIST_ENDPOINT, $tableName);
    }

    /**
     * @inheritdoc
     */
    public function getColumn($tableName, $columnName)
    {
        return $this->performRequest('GET', static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);
    }

    /**
     * @inheritdoc
     */
    public function getEntries($tableName, array $options = [])
    {
        return $this->performRequest('GET', static::TABLE_ENTRIES_ENDPOINT, $tableName, null, $options);
    }

    /**
     * @inheritdoc
     */
    public function getEntry($tableName, $id, array $options = [])
    {
        return $this->performRequest('GET', static::TABLE_ENTRY_ENDPOINT, [$tableName, $id]);
    }

    /**
     * @inheritdoc
     */
    public function getUsers(array $params = [])
    {
        return $this->getEntries('directus_users', $params);
    }

    /**
     * @inheritdoc
     */
    public function getUser($id, array $params = [])
    {
        return $this->getEntry($id, 'directus_users', $params);
    }

    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        return $this->performRequest('GET', static::GROUP_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getGroup($groupID)
    {
        return $this->performRequest('GET', static::GROUP_INFORMATION_ENDPOINT, $groupID);
    }

    /**
     * @inheritdoc
     */
    public function getGroupPrivileges($groupID)
    {
        return $this->performRequest('GET', static::GROUP_PRIVILEGES_ENDPOINT, $groupID);
    }

    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        return $this->performRequest('GET', static::FILE_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getFile($fileID)
    {
        return $this->performRequest('GET', static::FILE_INFORMATION_ENDPOINT, $fileID);
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return $this->performRequest('GET', static::SETTING_LIST_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsByCollection($collectionName)
    {
        return $this->performRequest('GET', static::SETTING_COLLECTION_ENDPOINT, $collectionName);
    }

    /**
     * @inheritdoc
     */
    public function getMessages($userId)
    {
        return $this->performRequest('GET', static::MESSAGES_USER_ENDPOINT, $userId);
    }

    /**
     * @inheritdoc
     */
    public function createEntry($tableName, array $data)
    {
        return $this->performRequest('POST', static::TABLE_ENTRY_CREATE_ENDPOINT, $tableName, $data);
    }

    /**
     * @inheritdoc
     */
    public function updateEntry($tableName, $id, array $data)
    {
        return $this->performRequest('PUT', static::TABLE_ENTRY_UPDATE_ENDPOINT, [$tableName, $id], $data);
    }

    /**
     * @inheritdoc
     */
    public function deleteEntry($tableName, $ids)
    {
        return $this->performRequest('DELETE', static::TABLE_ENTRY_DELETE_ENDPOINT, [$tableName, $ids]);
    }

    /**
     * @inheritdoc
     */
    public function createUser(array $data)
    {
        // @TODO: Add hooks
        if (ArrayUtils::has($data, 'password')) {
            // @NOTE: Use Directus password hash
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        return $this->createEntry('directus_users', $data);
    }

    /**
     * @inheritdoc
     */
    public function updateUser($id, array $data)
    {
        // @TODO: Add hooks
        if (ArrayUtils::has($data, 'password')) {
            // @NOTE: Use Directus password hash
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        return $this->updateEntry('directus_users', $id, $data);
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($ids)
    {
        return $this->deleteEntry('directus_users', $ids);
    }

    /**
     * @inheritdoc
     */
    public function createFile(array $data)
    {
        return $this->createEntry('directus_files', $data);
    }

    /**
     * @inheritdoc
     */
    public function updateFile($id, array $data)
    {
        return $this->updateEntry('directus_files', $id, $data);
    }

    /**
     * @inheritdoc
     */
    public function deleteFile($ids)
    {
        return $this->deleteEntry('directus_files', $ids);
    }
}
