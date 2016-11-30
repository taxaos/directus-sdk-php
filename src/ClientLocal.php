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
use Directus\Database\TableGateway\DirectusActivityTableGateway;
use Directus\Database\TableGateway\DirectusMessagesTableGateway;
use Directus\Database\TableGateway\DirectusPreferencesTableGateway;
use Directus\Database\TableGateway\DirectusPrivilegesTableGateway;
use Directus\Database\TableGateway\DirectusUiTableGateway;
use Directus\Database\TableGateway\DirectusUsersTableGateway;
use Directus\Database\TableGateway\RelationalTableGateway;
use Directus\Database\TableSchema;
use Directus\Util\ArrayUtils;
use Directus\Util\SchemaUtils;

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
     * @inheritdoc
     */
    public function createGroup(array $data)
    {
        return $this->createEntry('directus_groups', $data);
    }

    /**
     * @inheritdoc
     */
    public function createMessage(array $data)
    {
        $this->requiredAttributes(['from', 'message', 'subject'], $data);
        $this->requiredOneAttribute(['to', 'toGroup'], $data);

        $recipients = $this->getMessagesTo($data);
        $recipients = explode(',', $recipients);
        ArrayUtils::remove($data, ['to', 'toGroup']);

        $groupRecipients = [];
        $userRecipients = [];
        foreach ($recipients as $recipient) {
            $typeAndId = explode('_', $recipient);
            if ($typeAndId[0] == 0) {
                $userRecipients[] = $typeAndId[1];
            } else {
                $groupRecipients[] = $typeAndId[1];
            }
        }

        $ZendDb = $this->container->get('connection');
        $acl = $this->container->get('acl');
        if (count($groupRecipients) > 0) {
            $usersTableGateway = new DirectusUsersTableGateway($ZendDb, $acl);
            $result = $usersTableGateway->findActiveUserIdsByGroupIds($groupRecipients);
            foreach ($result as $item) {
                $userRecipients[] = $item['id'];
            }
        }

        $userRecipients[] = $acl->getUserId();

        $messagesTableGateway = new DirectusMessagesTableGateway($ZendDb, $acl);
        $id = $messagesTableGateway->sendMessage($data, array_unique($userRecipients), $acl->getUserId());

        if ($id) {
            $Activity = new DirectusActivityTableGateway($ZendDb, $acl);
            $data['id'] = $id;
            $Activity->recordMessage($data, $acl->getUserId());
        }

        $message = $messagesTableGateway->fetchMessageWithRecipients($id, $acl->getUserId());
        $response = [
            'meta' => [
                'type' => 'entry',
                'table' => 'directus_messages'
            ],
            'data' => $message
        ];

        return $this->createResponseFromData($response);
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(array $data)
    {
        return $this->createMessage($data);
    }

    public function createPrivileges(array $data)
    {
        $connection = $this->container->get('connection');
        $acl = $this->container->get('acl');
        $privileges = new DirectusPrivilegesTableGateway($connection, $acl);

        $response = [
            'meta' => [
                'type' => 'entry',
                'table' => 'directus_privileges'
            ],
            'data' => $privileges->insertPrivilege($data)
        ];

        return $this->createResponseFromData($response);
    }

    public function createTable($name, array $data = [])
    {
        $isTableNameAlphanumeric = preg_match("/[a-z0-9]+/i", $name);
        $zeroOrMoreUnderscoresDashes = preg_match("/[_-]*/i", $name);

        if (!($isTableNameAlphanumeric && $zeroOrMoreUnderscoresDashes)) {
            return $this->createResponseFromData(['error' => ['message' => 'invalid_table_name']]);
        }

        $schema = $this->container->get('schemaManager');
        $emitter = $this->container->get('emitter');
        if (!$schema->tableExists($name)) {
            $emitter->run('table.create:before', $name);
            // Through API:
            // Remove spaces and symbols from table name
            // And in lowercase
            $name = SchemaUtils::cleanTableName($name);
            $schema->createTable($name);
            $emitter->run('table.create', $name);
            $emitter->run('table.create:after', $name);
        }

        $connection = $this->container->get('connection');
        $acl = $this->container->get('acl');
        $privileges = new DirectusPrivilegesTableGateway($connection, $acl);

        return $this->createResponseFromData($privileges->insertPrivilege([
            'group_id' => 1,
            'table_name' => $name
        ]));
    }

    /**
     * @inheritdoc
     */
    public function createColumnUIOptions(array $data)
    {
        $this->requiredAttributes(['table', 'column', 'ui', 'options'], $data);
        $tableGateway = $this->getTableGateway('directus_ui');

        $table = $data['table'];
        $column = $data['column'];
        $ui = $data['ui'];

        $data = $data['options'];
        $keys = ['table_name' => $table, 'column_name' => $column, 'ui_name' => $ui];
        $uis = to_name_value($data, $keys);

        $column_settings = [];
        foreach ($uis as $col) {
            $existing = $tableGateway->select(['table_name' => $table, 'column_name' => $column, 'ui_name' => $ui, 'name' => $col['name']])->toArray();
            if (count($existing) > 0) {
                $col['id'] = $existing[0]['id'];
            }
            array_push($column_settings, $col);
        }
        $tableGateway->updateCollection($column_settings);

        $connection = $this->container->get('connection');
        $acl = $this->container->get('acl');
        $UiOptions = new DirectusUiTableGateway($connection, $acl);
        $response = $UiOptions->fetchOptions($table, $column, $ui);

        if (!$response) {
            $response = [
                'error' => [
                    'message' => sprintf('unable_to_find_column_%s_options_for_%s', ['column' => $column, 'ui' => $ui])
                ],
                'success' => false
            ];
        } else {
            $response = [
                'meta' => [
                    'type' => 'entry',
                    'table' => 'directus_ui'
                ],
                'data' => $response
            ];
        }

        return $this->createResponseFromData($response);
    }

    public function getPreferences($table, $user)
    {
        $acl = $this->container->get('acl');
        $connection = $this->container->get('connection');
        $preferencesTableGateway = new DirectusPreferencesTableGateway($connection, $acl);

        $response = [
            'meta' => [
                'type' => 'entry',
                'table' => 'directus_preferences'
            ],
            'data' => $preferencesTableGateway->fetchByUserAndTableAndTitle($user, $table)
        ];

        return $this->createResponseFromData($response);
    }

    /**
     * @inheritdoc
     */
    public function deleteBookmark($id)
    {
        return $this->deleteEntry('directus_bookmarks', $id);
    }

    /**
     * @inheritdoc
     */
    public function deleteColumn($name, $table)
    {
        $tableGateway = $this->getTableGateway($table);
        $success = $tableGateway->dropColumn($name);

        $response = [
            'success' => (bool) $success
        ];

        if (!$success) {
            $response['error'] = [
                'message' => sprintf('unable_to_remove_column_%s', ['column_name' => $name])
            ];
        }

        return $this->createResponseFromData($response);
    }

    /**
     * @inheritdoc
     */
    public function deleteGroup($id)
    {
        return $this->deleteEntry('directus_groups', $id);
    }

    /**
     * @inheritdoc
     */
    public function deleteTable($name)
    {
        $tableGateway = $this->getTableGateway($name);
        $success = $tableGateway->drop();

        $response = [
            'success' => (bool) $success
        ];

        if (!$success) {
            $response['error'] = [
                'message' => sprintf('unable_to_remove_table_%s', ['table_name' => $name])
            ];
        }

        return $this->createResponseFromData($response);
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
