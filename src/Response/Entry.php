<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK\Response;

/**
 * Entry
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class Entry implements ResponseInterface, \ArrayAccess
{
    /**
     * @var array
     */
    protected $data = null;

    /**
     * @var array
     */
    protected $metadata = null;

    /**
     * Entry constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the entry data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the entry metadata
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metadata;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new \InvalidArgumentException('Invalid property: ' . $name);
    }

    /**
     * Gets the object representation of this entry
     *
     * @return object
     */
    public function jsonSerialize()
    {
        return (object) [
            'metadata' => $this->getMetaData(),
            'fields' => $this->getData()
        ];
    }
}