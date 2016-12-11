<?php

class EntryCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Directus\SDK\Response\EntryCollection
     */
    protected $collection;

    public function setUp()
    {
        $this->data = [
            'Active' => 2,
            'Draft' => 0,
            'Delete' => 0,
            'total' => 2,
            'rows' => [
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'age' => 34
                ],
                [
                    'id' => 2,
                    'first_name' => 'Joseph',
                    'last_name' => 'Row',
                    'age' => 54
                ],
            ]
        ];

        $this->collection = new \Directus\SDK\Response\EntryCollection($this->data);
    }

    public function testCollection()
    {
        $collection = $this->collection;

        $this->assertEquals($this->data, $collection->getRawData());
        $this->assertInternalType('array', $collection->getData());
        $this->assertCount(2, $collection->getData());
        $this->assertNotNull($collection->getMetaData());
    }

    public function testCount()
    {
        $this->assertSame(2, count($this->collection));
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\ArrayIterator', $this->collection->getIterator());
    }

    public function testJson()
    {
        $json = json_encode($this->collection);
        $array = json_decode($json, true);

        $this->assertInternalType('array', $array);
        $this->assertSame(2, count($array['data']));
    }
}