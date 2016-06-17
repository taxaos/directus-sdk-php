<?php

namespace Directus\SDK;

interface RequestsInterface
{
    /**
     * Fetch list of tables
     * @return array
     */
    public function fetchTables();

    /**
     * Fetch Information of a given table
     * @param $tableName
     * @return object
     */
    public function fetchTableInfo($tableName);

    /**
     * Fetch columns of a given table
     * @param $tableName
     * @return array
     */
    public function fetchColumns($tableName);

    /**
     * Fetch details of a given table's column
     * @param $tableName
     * @param $columnName
     * @return array
     */
    public function fetchColumnInfo($tableName, $columnName);

    /**
     * Fetch Items from a given table
     * @param $tableName
     * @return object
     */
    public function fetchItems($tableName);

    /**
     * Fetch an Item in a given table by ID
     * @param $tableName
     * @param $itemID
     * @return array
     */
    public function fetchItem($tableName, $itemID);

    /**
     * Fetch List of User groups
     * @return object
     */
    public function fetchGroups();

    /**
     * Fetch the information of a given user group
     * @param $groupID
     * @return object
     */
    public function fetchGroupInfo($groupID);

    /**
     * Fetch a given group privileges
     * @param $groupID
     * @return object
     */
    public function fetchGroupPrivileges($groupID);

    /**
     * Fetch list of files
     * @return object
     */
    public function fetchFiles();

    /**
     * Fetch the information of a given file
     * @param $fileID
     * @return mixed
     */
    public function fetchFileInfo($fileID);

    /**
     * Fetch all settings
     * @return object
     */
    public function fetchSettings();

    /**
     * Fetch all settings in a given collection name
     * @param $collectionName
     * @return object
     */
    public function fetchSettingCollection($collectionName);
}
