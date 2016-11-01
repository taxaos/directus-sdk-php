<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;
use Directus\SDK\Response\EntryCollection;

/**
 * Requests Interface
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
interface RequestsInterface
{
    /**
     * Gets list of all tables
     *
     * @param array $params
     *
     * @return array
     */
    public function getTables(array $params = []);

    /**
     * Gets list of the all columns
     *
     * @param array $params
     *
     * @return array
     */
    public function getColumns(array $params = []);

    /**
     * Fetch columns of a given table
     *
     * @param $tableName
     * @param $params
     *
     * @return array
     */
    public function getTableColumns($tableName, array $params = []);

    /**
     * Fetch Information of a given table
     *
     * @param $tableName
     *
     * @return object
     */
    public function fetchTableInfo($tableName);

    /**
     * Fetch details of a given table's column
     *
     * @param $tableName
     * @param $columnName
     *
     * @return array
     */
    public function fetchColumnInfo($tableName, $columnName);

    /**
     * Fetch Items from a given table
     *
     * @param string $tableName
     * @param array $options
     *
     * @return object
     */
    public function getEntries($tableName, array $options = []);

    /**
     * Get an entry in a given table by the given ID
     *
     * @param mixed $id
     * @param string $tableName
     * @param array $options
     *
     * @return array
     */
    public function getEntry($tableName, $id, array $options = []);

    /**
     * Gets the list of users
     *
     * @param array $params
     *
     * @return array
     */
    public function getUsers(array $params = []);

    /**
     * Gets a user by the given id
     *
     * @param $id
     * @param array $params
     *
     * @return array
     */
    public function getUser($id, array $params = []);

    /**
     * Fetch List of User groups
     *
     * @return object
     */
    public function fetchGroups();

    /**
     * Fetch the information of a given user group
     *
     * @param $groupID
     *
     * @return object
     */
    public function fetchGroupInfo($groupID);

    /**
     * Fetch a given group privileges
     *
     * @param $groupID
     *
     * @return object
     */
    public function fetchGroupPrivileges($groupID);

    /**
     * Gets a list fo files
     *
     * @return object
     */
    public function getFiles();

    /**
     * Gets the information of a given file ID
     *
     * @param $fileID
     *
     * @return mixed
     */
    public function getFile($fileID);

    /**
     * Fetch all settings
     *
     * @return object
     */
    public function fetchSettings();

    /**
     * Fetch all settings in a given collection name
     *
     * @param $collectionName
     *
     * @return object
     */
    public function fetchSettingCollection($collectionName);

    /**
     * Gets all messages from the given user ID
     *
     * @param $userId
     *
     * @return EntryCollection
     */
    public function getMessages($userId);

    public function createEntry($tableName, array $data);
}
