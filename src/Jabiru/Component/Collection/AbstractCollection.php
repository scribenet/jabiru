<?php

namespace Scribe\Jabiru\Component\Collection;

abstract class AbstractCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $values;

    /**
     * Constructor
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $this->values = $values;
    }

    /**
     * Appends a new value to end of collection
     *
     * @param  mixed $value The value to append
     * @return AbstractCollection
     */
    public function add($value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Assigns a new value in the collection at the specified index
     *
     * @param  string $index The index
     * @param  mixed  $value The value
     * @return AbstractCollection
     */
    public function set($index, $value)
    {
        $this->values[ (string) $index ] = $value;

        return $this;
    }

    /**
     * Returns the value at the specified index
     *
     * @param  string $index The index
     * @throws \OutOfBoundsException If the index does not exist
     * @return mixed
     */
    public function get($index)
    {
        if (false === $this->exists($index)) {
            throw new \OutOfBoundsException(sprintf('Undefined offset "%s"', $index));
        }

        return $this->values[ (string) $index ];
    }

    /**
     * Checks if the requested index exists in the collection
     *
     * @param  string $index The index
     * @return bool
     */
    public function exists($index)
    {
        return (bool) isset($this->values[ (string) $index ]);
    }

    /**
     * Checks if the requested value exists in the collections
     *
     * @param  mixed $value The value
     * @return bool
     */
    public function contains($value)
    {
        return array_search($value, $this->values, true) !== false;
    }

    /**
     * Unset the requested index within the collection
     *
     * @param  string $index The index
     * @return AbstractCollection
     */
    public function remove($index)
    {
        if ($this->exists($index)) {
            unset($this->values[$index]);
        }

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable An instance of an value implementing \Iterator or \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * Count elements within collection
     *
     * @return int The custom count as an integer. The return value is cast to an integer.
     */
    public function count()
    {
        return (int) count($this->values);
    }

}