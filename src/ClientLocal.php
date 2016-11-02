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
use Directus\SDK\Response\EntryCollection;
use Directus\SDK\Response\Entry;

/**
 * Client Local
 *
 * Client to Interact with the database directly using Directus Database Component
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class ClientLocal implements RequestsInterface
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
        return $this->createResponseFromData(TableSchema::getColumnSchemaArray($tableName, $columnName));
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
        $newRecord = $tableGateway->manageRecordUpdate($tableName, $data);

        return $this->createResponseFromData($newRecord->toArray());
    }

    /**
     * @inheritDoc
     */
    public function updateEntry($tableName, $id, array $data)
    {
        $tableGateway = $this->getTableGateway($tableName);
        $record = $tableGateway->manageRecordUpdate($tableName, array_merge($data, ['id' => $id]));

        return $this->createResponseFromData($record->toArray());
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
    public function createFile(array $data)
    {
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

    // @TODO: move to a builder class
    protected function createResponseFromData($data)
    {
        if (isset($data['rows'])) {
            $response = new EntryCollection($data);
        } else {
            $response = new Entry($data);
        }

        return $response;
    }
}
