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
use Zend\Db\Sql\Select;

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
     * Gets the list of tables name in the database
     *
     * @param array $params
     *
     * @return array
     */
    public function getTables(array $params = [])
    {
        return TableSchema::getTablesSchema($params);
    }

    /**
     * Gets all the columns in the database
     *
     * @param array $params
     *
     * @return array
     */
    public function getColumns(array $params = [])
    {
        return TableSchema::getColumnsSchema($params);
    }

    /**
     * Gets table columns
     *
     * @param $tableName
     * @param array $params
     *
     * @return \Directus\Database\Object\Column[]
     */
    public function getTableColumns($tableName, array $params = [])
    {
        $tables = TableSchema::getTableColumnsSchema($tableName, $params);

        return $tables;
    }

    /**
     * Gets all the entries in the given table name
     *
     * @param string $tableName
     * @param array $params
     *
     * @return Entry|EntryCollection
     */
    public function getEntries($tableName, array $params = [])
    {
        $tableGateway = $this->getTableGateway($tableName);

        return $this->createResponseFromData($tableGateway->getEntries($params));
    }

    /**
     * Gets an entry in the given table name with the given id
     *
     * @param string $tableName
     * @param mixed $id
     * @param array $params
     *
     * @return array|mixed
     */
    public function getEntry($tableName, $id, array $params = [])
    {
        // @TODO: Dynamic ID
        return $this->getEntries($tableName, array_merge($params, [
            'id' => $id
        ]));
    }

    /**
     * Gets the list of users
     *
     * @param array $params
     *
     * @return array|mixed
     */
    public function getUsers(array $params = [])
    {
        // @TODO: store the directus tables somewhere (SchemaManager?)
        return $this->getEntries('directus_users', $params);
    }

    /**
     * Gets an user by the given id
     *
     * @param $id
     * @param array $params
     *
     * @return array|mixed
     */
    public function getUser($id, array $params = [])
    {
        return $this->getEntry('directus_users', $id, $params);
    }

    /**
     * @inheritDoc
     */
    public function fetchTables()
    {
        // TODO: Implement fetchTables() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchTableInfo($tableName)
    {
        // TODO: Implement fetchTableInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchColumns($tableName)
    {
        // TODO: Implement fetchColumns() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchColumnInfo($tableName, $columnName)
    {
        // TODO: Implement fetchColumnInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchItems($tableName = null, $conditions = [])
    {
        if ($tableName == null) {
            $tableName = $this->getTable();
        }

        $select = new Select($tableName);

        // Conditional to honor the active column, (does not check if column exists)
        if (isset($conditions['active'])) {
            $select->where->equalTo('active', $conditions['active']);
        }

        // Order by "id desc" by default or by a parameter value
        if (isset($conditions['sort'])) {
            $select->order($conditions['sort']);
        }

        return $this->selectWith($select);
    }

    /**
     * @inheritDoc
     */
    public function fetchItem($tableName, $itemID)
    {
        // TODO: Implement fetchItem() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchGroups()
    {
        // TODO: Implement fetchGroups() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchGroupInfo($groupID)
    {
        // TODO: Implement fetchGroupInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchGroupPrivileges($groupID)
    {
        // TODO: Implement fetchGroupPrivileges() method.
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
    public function fetchSettings()
    {
        // TODO: Implement fetchSettings() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchSettingCollection($collectionName)
    {
        // TODO: Implement fetchSettingCollection() method.
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
