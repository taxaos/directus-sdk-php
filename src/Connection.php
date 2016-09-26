<?php

namespace Directus\SDK;

use Zend\Db\Adapter\Adapter;

class Connection extends Adapter
{
    /**
     * Name of the connection driver
     *
     * @var string
     */
    protected $driverName;

    /**
     * Database configuration
     *
     * @var array
     */
    protected $config = [];

    /**
     * Connection constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        parent::__construct($config);
    }

    protected function createDriver($parameters)
    {
        $driver = parent::createDriver($parameters);
        $driverName = strtolower($parameters['driver']);

        if ($this->isAnPDODriver($driverName)) {
            $driverName = substr($driverName, 4);
        }

        $this->driverName = $driverName;

        return $driver;
    }

    /**
     * Checks if a given driver name is an pdo driver.
     *
     * @param $driverName
     *
     * @return bool
     */
    protected function isAnPDODriver($driverName)
    {
        return strrpos($driverName, 'pdo_', -strlen($driverName)) !== FALSE;
    }

    /**
     * Map all calls to the driver connection object.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getDriver()->getConnection(), $name], $arguments);
    }
}
