<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\SDK\Response\Entry;
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
     * @return EntryCollection
     */
    public function getTables(array $params = []);

    /**
     * Gets the details of the given table
     *
     * @param $tableName
     *
     * @return Entry
     */
    public function getTable($tableName);

    /**
     * Gets columns of a given table
     *
     * @param $tableName
     * @param $params
     *
     * @return EntryCollection
     */
    public function getColumns($tableName, array $params = []);

    /**
     * Gets the details of a given table's column
     *
     * @param $tableName
     * @param $columnName
     *
     * @return Entry
     */
    public function getColumn($tableName, $columnName);

    /**
     * Fetch Items from a given table
     *
     * @param string $tableName
     * @param array $options
     *
     * @return EntryCollection
     */
    public function getEntries($tableName, array $options = []);

    /**
     * Get an entry in a given table by the given ID
     *
     * @param mixed $id
     * @param string $tableName
     * @param array $options
     *
     * @return Entry
     */
    public function getEntry($tableName, $id, array $options = []);

    /**
     * Gets the list of users
     *
     * @param array $params
     *
     * @return EntryCollection
     */
    public function getUsers(array $params = []);

    /**
     * Gets a user by the given id
     *
     * @param $id
     * @param array $params
     *
     * @return Entry
     */
    public function getUser($id, array $params = []);

    /**
     * Gets a list of User groups
     *
     * @return EntryCollection
     */
    public function getGroups();

    /**
     * Gets the information of a given user group
     *
     * @param $groupID
     *
     * @return Entry
     */
    public function getGroup($groupID);

    /**
     * Get a given group privileges
     *
     * @param $groupID
     *
     * @return EntryCollection
     */
    public function getGroupPrivileges($groupID);

    /**
     * Gets a list fo files
     *
     * @return EntryCollection
     */
    public function getFiles();

    /**
     * Gets the information of a given file ID
     *
     * @param $fileID
     *
     * @return Entry
     */
    public function getFile($fileID);

    /**
     * Gets all settings
     *
     * @return object
     */
    public function getSettings();

    /**
     * Gets all settings in a given collection name
     *
     * @param $collectionName
     *
     * @return EntryCollection
     */
    public function getSettingsByCollection($collectionName);

    /**
     * Gets all messages from the given user ID
     *
     * @param $userId
     *
     * @return EntryCollection
     */
    public function getMessages($userId);

    /**
     * Create a new entry in the given table name
     *
     * @param $tableName
     * @param array $data
     *
     * @return Entry
     */
    public function createEntry($tableName, array $data);

    /**
     * Update the entry of the given table and id
     *
     * @param $tableName
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateEntry($tableName, $id, array $data);

    /**
     * Deletes the given entry id(s)
     *
     * @param $tableName
     * @param string|array|Entry|EntryCollection $ids
     *
     * @return int
     */
    public function deleteEntry($tableName, $ids);

    /**
     * Creates a new user
     *
     * @param array $data
     *
     * @return Entry
     */
    public function createUser(array $data);

    /**
     * Updates the given user id
     *
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateUser($id, array $data);

    /**
     * Deletes the given user id(s)
     *
     * @param string|array|Entry|EntryCollection $ids
     *
     * @return int
     */
    public function deleteUser($ids);

    /**
     * Creates a new file
     *
     * @param File $file
     *
     * @return Entry
     */
    public function createFile(File $file);

    /**
     * Updates the given file id
     *
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateFile($id, array $data);

    /**
     * Deletes the given file id(s)
     *
     * @param string|array|Entry|EntryCollection $ids
     *
     * @return int
     */
    public function deleteFile($ids);

    /**
     * Creates a new Bookmark
     *
     * @param $data
     *
     * @return Entry
     */
    public function createBookmark($data);

    /**
     * Creates a new Table preferences
     *
     * @param $data
     *
     * @return Entry
     */
    public function createPreferences($data);

    /**
     * Creates a new Column
     *
     * @param $data
     *
     * @return Entry
     */
    public function createColumn($data);

    /**
     * Creates a new group
     *
     * @param $data
     *
     * @return Entry
     */
    public function createGroup(array $data);
}
