<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\Database\Connection;
use Directus\Database\TableGateway\BaseTableGateway;
use Directus\Database\TableGateway\DirectusMessagesTableGateway;
use Directus\Database\TableGateway\RelationalTableGateway;
use Directus\Database\TableSchema;
use Directus\Util\ArrayUtils;

/**
 * Client Local
 *
 * Client to Interact with the database directly using Directus Database Component
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientLocal extends AbstractClient
{
    /**
     * @var BaseTableGateway[]
     */
    protected $tableGateways = [];

    /**
     * @var Connection
     */
    protected $connection = null;

    /**
     * ClientLocal constructor.
     *
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getTables(array $params = [])
    {
        return $this->createResponseFromData(TableSchema::getTablesSchema($params));
    }

    /**
     * @inheritDoc
     */
    public function getTable($tableName)
    {
        return $this->createResponseFromData(TableSchema::getSchemaArray($tableName));
    }

    /**
     * @inheritDoc
     */
    public function getColumns($tableName, array $params = [])
    {
        return $this->createResponseFromData(TableSchema::getColumnSchemaArray($tableName, $params));
    }

    /**
     * @inheritDoc
     */
    public function getColumn($tableName, $columnName)
    {
        return $this->createResponseFromData(TableSchema::getColumnSchema($tableName, $columnName)->toArray());
    }

    /**
     * @inheritDoc
     */
    public function getEntries($tableName, array $params = [])
    {
        $tableGateway = $this->getTableGateway($tableName);

        return $this->createResponseFromData($tableGateway->getEntries($params));
    }

    /**
     * @inheritDoc
     */
    public function getEntry($tableName, $id, array $params = [])
    {
        // @TODO: Dynamic ID
        return $this->getEntries($tableName, array_merge($params, [
            'id' => $id
        ]));
    }

    /**
     * @inheritDoc
     */
    public function getUsers(array $params = [])
    {
        // @TODO: store the directus tables somewhere (SchemaManager?)
        return $this->getEntries('directus_users', $params);
    }

    /**
     * @inheritDoc
     */
    public function getUser($id, array $params = [])
    {
        return $this->getEntry('directus_users', $id, $params);
    }

    /**
     * @inheritDoc
     */
    public function getGroups(array $params = [])
    {
        return $this->getEntries('directus_groups', $params);
    }

    /**
     * @inheritDoc
     */
    public function getGroup($id, array $params = [])
    {
        return $this->getEntry('directus_groups', $id, $params);
    }

    /**
     * @inheritDoc
     */
    public function getGroupPrivileges($groupID)
    {
        $this->getEntries('directus_privileges', [
            'filter' => [
                'group_id' => ['eq' => $groupID]
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getFiles(array $params = [])
    {
        return $this->getEntries('directus_files', $params);
    }

    /**
     * @inheritDoc
     */
    public function getFile($id, array $params = [])
    {
        return $this->getEntry('directus_files', $id, $params);
    }

    /**
     * @inheritDoc
     */
    public function getSettings()
    {
        return $this->getEntries('directus_settings');
    }

    /**
     * @inheritDoc
     */
    public function getSettingsByCollection($collectionName)
    {
        return $this->getEntries('directus_settings', [
            'filter' => [
                'collection' => ['eq' => $collectionName]
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessages($userId)
    {
        $messagesTableGateway = new DirectusMessagesTableGateway($this->connection, null);
        $result = $messagesTableGateway->fetchMessagesInboxWithHeaders($userId);

        return $this->createResponseFromData($result);
    }

    /**
     * @inheritDoc
     */
    public function createEntry($tableName, array $data)
    {
        $tableGateway = $this->getTableGateway($tableName);
        $data = $this->processData($tableName, $data);

        foreach($data as $key => $value) {
            if ($value instanceof File) {
                $data[$key] = $this->processFile($value);
            }
        }

        $newRecord = $tableGateway->manageRecordUpdate($tableName, $data);

        return $this->getEntry($tableName, $newRecord[$tableGateway->primaryKeyFieldName]);
    }

    /**
     * @inheritDoc
     */
    public function updateEntry($tableName, $id, array $data)
    {
        $tableGateway = $this->getTableGateway($tableName);
        $data = $this->processData($tableName, $data);

        foreach($data as $key => $value) {
            if ($value instanceof File) {
                $data[$key] = $this->processFile($value);
            }
        }

        $updatedRecord = $tableGateway->manageRecordUpdate($tableName, array_merge($data, ['id' => $id]));

        return $this->getEntry($tableName, $updatedRecord[$tableGateway->primaryKeyFieldName]);
    }

    /**
     * @inheritDoc
     */
    public function deleteEntry($tableName, $ids)
    {
        // @TODO: Accept EntryCollection and Entry
        $tableGateway = $this->getTableGateway($tableName);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return $tableGateway->delete(function($delete) use ($ids) {
            return $delete->where->in('id', $ids);
        });
    }

    /**
     * @inheritDoc
     */
    public function createUser(array $data)
    {
        return $this->createEntry('directus_users', $data);
    }

    /**
     * @inheritDoc
     */
    public function updateUser($id, array $data)
    {
        return $this->updateEntry('directus_users', $id, $data);
    }

    /**
     * @inheritDoc
     */
    public function deleteUser($ids)
    {
        return $this->deleteEntry('directus_users', $ids);
    }

    /**
     * @inheritDoc
     */
    public function createFile(File $file)
    {
        $data = $this->processFile($file);

        return $this->createEntry('directus_files', $data);
    }

    /**
     * @inheritDoc
     */
    public function updateFile($id, array $data)
    {
        return $this->updateEntry('directus_files', $id, $data);
    }

    /**
     * @inheritDoc
     */
    public function deleteFile($ids)
    {
        return $this->deleteEntry('directus_files', $ids);
    }

    public function createPreferences($data)
    {
        if (!ArrayUtils::contains($data, ['title', 'table_name'])) {
            throw new \Exception('title and table_name are required');
        }

        $acl = $this->container->get('acl');
        $data['user'] = $acl->getUserId();

        return $this->createEntry('directus_preferences', $data);
    }

    /**
     * @inheritdoc
     */
    public function createBookmark($data)
    {
        $acl = $this->container->get('acl');
        $data['user'] = $acl->getUserId();

        $preferences = $this->createPreferences(ArrayUtils::pick($data, [
            'title', 'table_name', 'sort', 'status', 'search_string', 'sort_order', 'columns_visible', 'user'
        ]));

        $title = $preferences->title;
        $tableName = $preferences->table_name;
        $bookmarkData = [
            'section' => 'search',
            'title' => $title,
            'url' => 'tables/' . $tableName . '/pref/' . $title,
            'user' => $data['user']
        ];

        return $this->createEntry('directus_bookmarks', $bookmarkData);
    }

    /**
     * @inheritdoc
     */
    public function createColumn($data)
    {
        $data = $this->parseColumnData($data);

        $tableGateway = $this->getTableGateway($data['table_name']);

        $tableGateway->addColumn($data['table_name'], ArrayUtils::omit($data, ['table_name']));

        return $this->getColumn($data['table_name'], $data['column_name']);
    }

    /**
     * Get a table gateway for the given table name
     *
     * @param $tableName
     *
     * @return RelationalTableGateway
     */
    protected function getTableGateway($tableName)
    {
        if (!array_key_exists($tableName, $this->tableGateways)) {
            $acl = TableSchema::getAclInstance();
            $this->tableGateways[$tableName] = new RelationalTableGateway($tableName, $this->connection, $acl);
        }

        return $this->tableGateways[$tableName];
    }
}
