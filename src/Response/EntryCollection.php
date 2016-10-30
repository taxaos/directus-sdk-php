<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK\Response;

use Directus\Util\ArrayUtils;

/**
 * Entry Collection
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class EntryCollection implements ResponseInterface, \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $rawData = [];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * EntryCollection constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->rawData = $data;
        $this->metadata = ArrayUtils::omit($data, 'rows');

        $rows = isset($data['rows']) ? $data['rows'] : [];
        $items = [];
        foreach($rows as $row) {
            $items[] = new Entry($row);
        }

        $this->items = $items;
    }

    /**
     * Get the response raw data
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Get the response entries
     *
     * @return array
     */
    public function getData()
    {
        return $this->items;
    }

    /**
     * Get the response metadata
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metadata;
    }

    /**
     * Create a new iterator based on this collection
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('EntryCollection is read only');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('EntryCollection is read only');
    }

    /**
     * Gets the number of entries in this collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Gets an object representation of this collection
     *
     * @return object
     */
    public function jsonSerialize()
    {
        return (object) [
            'metadata' => $this->metadata,
            'data' => $this->items
        ];
    }
}