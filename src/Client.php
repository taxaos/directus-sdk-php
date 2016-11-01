<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK;

use Directus\Acl\Acl;
use Directus\Database\Connection;
use Directus\Util\ArrayUtils;
use Directus\Database\TableSchema;

/**
 * Client
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class Client
{
    /**
     * @var Client
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $defaultConfig = [
        'environment' => 'development',
        'database' => [
            'driver' => 'pdo_mysql'
        ],
        'status' => [
            'column_name' => 'active',
            'deleted_value' => 0,
            'active_value' => 1,
            'draft_value' => 2,
            'mapping' => [
                0 => [
                    'name' => 'Delete',
                    'color' => '#C1272D',
                    'sort' => 3
                ],
                1 => [
                    'name' => 'Active',
                    'color' => '#5B5B5B',
                    'sort' => 1
                ],
                2 => [
                    'name' => 'Draft',
                    'color' => '#BBBBBB',
                    'sort' => 2
                ]
            ]
        ]
    ];

    /**
     * @param $userToken
     * @param array $options
     *
     * @return ClientLocal|ClientRemote
     */
    public static function create($userToken, $options = [])
    {
        if (static::$instance == null) {
            static::$instance = new static;
        }

        if (!is_array($userToken)) {
            $newClient = static::$instance->createRemoteClient($userToken, $options);
        } else {
            $options = $userToken;
            $newClient = static::$instance->createLocalClient($options);
        }

        return $newClient;
    }

    /**
     * Creates a new local client instance
     *
     * @param array $options
     *
     * @return ClientLocal
     */
    public function createLocalClient(array $options)
    {
        $options = ArrayUtils::defaults($this->defaultConfig, $options);
        $dbConfig = ArrayUtils::get($options, 'database', []);

        $config = ArrayUtils::omit($options, 'database');
        // match the actual directus status mapping config key
        $config['statusMapping'] = $config['status']['mapping'];
        unset($config['status']['mapping']);

        if (!defined('STATUS_DELETED_NUM')) {
            define('STATUS_DELETED_NUM', ArrayUtils::get($config, 'status.deleted_value', 0));
        }

        if (!defined('STATUS_ACTIVE_NUM')) {
            define('STATUS_ACTIVE_NUM', ArrayUtils::get($config, 'status.active_value', 1));
        }

        if (!defined('STATUS_DRAFT_NUM')) {
            define('STATUS_DRAFT_NUM', ArrayUtils::get($config, 'status.draft_value', 2));
        }

        if (!defined('STATUS_COLUMN_NAME')) {
            define('STATUS_COLUMN_NAME', ArrayUtils::get($config, 'status.column_name', 'active'));
        }

        if (!defined('DIRECTUS_ENV')) {
            define('DIRECTUS_ENV', ArrayUtils::get($config, 'environment', 'development'));
        }

        $connection = new Connection($dbConfig);
        $connection->connect();

        $acl = new Acl();
        $acl->setUserId(1);
        $acl->setGroupId(1);
        $acl->setGroupPrivileges([
            '*' => [
                'id' => 1,
                'table_name' => '*',
                'group_id' => 1,
                'read_field_blacklist' => [],
                'write_field_blacklist' => [],
                'nav_listed' => 1,
                'status_id' => 0,
                'allow_view' => 2,
                'allow_add' => 1,
                'allow_edit' => 2,
                'allow_delete' => 2,
                'allow_alter' => 1
            ]
        ]);

        $schema = new \Directus\Database\Schemas\Sources\MySQLSchema($connection);
        $schema = new \Directus\Database\SchemaManager($schema);
        TableSchema::setSchemaManagerInstance($schema);
        TableSchema::setAclInstance($acl);
        TableSchema::setConnectionInstance($connection);
        TableSchema::setConfig($config);

        return new ClientLocal($connection);
    }

    /**
     * Create a new remote client instance
     *
     * @param $userToken
     * @param array $options
     *
     * @return ClientRemote
     */
    public function createRemoteClient($userToken, array $options = [])
    {
        return new ClientRemote($userToken, $options);
    }
}