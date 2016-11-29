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
use Directus\Util\StringUtils;

/**
 * Client Remote
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientRemote extends BaseClientRemote
{
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
        $path = $this->buildPath(static::TABLE_INFORMATION_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getColumns($tableName, array $params = [])
    {
        $path = $this->buildPath(static::COLUMN_LIST_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getColumn($tableName, $columnName)
    {
        $path = $this->buildPath(static::COLUMN_INFORMATION_ENDPOINT, [$tableName, $columnName]);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getEntries($tableName, array $options = [])
    {
        $path = $this->buildPath(static::TABLE_ENTRIES_ENDPOINT, $tableName);

        return $this->performRequest('GET', $path, ['query' => $options]);
    }

    /**
     * @inheritdoc
     */
    public function getEntry($tableName, $id, array $options = [])
    {
        $path = $this->buildPath(static::TABLE_ENTRY_ENDPOINT, [$tableName, $id]);

        return $this->performRequest('GET', $path, ['query' => $options]);
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
        $path = $this->buildPath(static::GROUP_INFORMATION_ENDPOINT, $groupID);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getGroupPrivileges($groupID)
    {
        $path = $this->buildPath(static::GROUP_PRIVILEGES_ENDPOINT, $groupID);

        return $this->performRequest('GET', $path);
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
        $path = $this->buildPath(static::FILE_INFORMATION_ENDPOINT, $fileID);

        return $this->performRequest('GET', $path);
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
        $path = $this->buildPath(static::SETTING_COLLECTION_ENDPOINT, $collectionName);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function getMessages($userId)
    {
        $path = $this->buildPath(static::MESSAGES_USER_ENDPOINT, $userId);

        return $this->performRequest('GET', $path);
    }

    /**
     * @inheritdoc
     */
    public function createEntry($tableName, array $data)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_CREATE_ENDPOINT, $tableName);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('POST', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function updateEntry($tableName, $id, array $data)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_UPDATE_ENDPOINT, [$tableName, $id]);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('PUT', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function deleteEntry($tableName, $id)
    {
        $path = $this->buildPath(static::TABLE_ENTRY_DELETE_ENDPOINT, [$tableName, $id]);

        return $this->performRequest('DELETE', $path);
    }

    /**
     * @inheritdoc
     */
    public function createUser(array $data)
    {
        return $this->createEntry('directus_users', $data);
    }

    /**
     * @inheritdoc
     */
    public function updateUser($id, array $data)
    {
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
    public function createFile(File $file)
    {
        $data = $this->processFile($file);

        return $this->performRequest('POST', static::FILE_CREATE_ENDPOINT, ['body' => $data]);
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
    public function deleteFile($id)
    {
        return $this->deleteEntry('directus_files', $id);
    }

    public function createPreferences($data)
    {
        $this->requiredAttributes(['title', 'table_name'], $data);

        $tableName = ArrayUtils::get($data, 'table_name');
        $path = $this->buildPath(static::TABLE_PREFERENCES_ENDPOINT, $tableName);
        $data = $this->processData($tableName, $data);

        return $this->performRequest('POST', $path, ['body' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function createBookmark($data)
    {
        $preferences = $this->createPreferences(ArrayUtils::pick($data, [
            'title', 'table_name', 'sort', 'status', 'search_string', 'sort_order', 'columns_visible'
        ]));

        $title = $preferences->title;
        $tableName = $preferences->table_name;
        $bookmarkData = [
            'section' => 'search',
            'title' => $title,
            'url' => 'tables/' . $tableName . '/pref/' . $title
        ];

        $path = $this->buildPath(static::TABLE_BOOKMARKS_CREATE_ENDPOINT);
        $bookmarkData = $this->processData($tableName, $bookmarkData);

        return $this->performRequest('POST', $path, ['body' => $bookmarkData]);
    }

    /**
     * @inheritdoc
     */
    public function createColumn($data)
    {
        $data = $this->parseColumnData($data);

        return $this->performRequest('POST', $this->buildPath(static::COLUMN_CREATE_ENDPOINT, $data['table_name']), [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createGroup(array $data)
    {
        return $this->performRequest('POST', static::GROUP_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createMessage(array $data)
    {
        $this->requiredAttributes(['from', 'message', 'subject'], $data);
        $this->requiredOneAttribute(['to', 'toGroup'], $data);

        $data['recipients'] = $this->getMessagesTo($data);
        ArrayUtils::remove($data, ['to', 'toGroup']);

        return $this->performRequest('POST', static::MESSAGES_CREATE_ENDPOINT, [
            'body' => $data
        ]);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(array $data)
    {
        return $this->createMessage($data);
    }
}
