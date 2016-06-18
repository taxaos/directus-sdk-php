<?php

namespace Directus\SDK;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class BaseTableGateway extends TableGateway implements RequestsInterface
{
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
        if (isset($conditions['active'])){
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
    public function fetchFiles()
    {
        // TODO: Implement fetchFiles() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchFileInfo($fileID)
    {
        // TODO: Implement fetchFileInfo() method.
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

}
