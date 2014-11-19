<?php

namespace Scribe\Jabiru\Component\Collection;

class Collection extends AbstractCollection
{
    /**
     * Join array elements with a string
     *
     * @param  string $glue Defaults to an empty string
     * @return string
     */
    public function join($glue = '')
    {
        return implode($glue, $this->values);
    }

    /**
     * Extract a slice of the array
     *
     * @param integer $offset If offset is non-negative, the sequence will start at that offset in the array.
     *                        If offset is negative, the sequence will start that far from the end of the array.
     * @param integer $length If length is given and is positive, then the sequence will have up to that many elements in it.
     *                        If the array is shorter than the length, then only the available array elements will be present.
     *                        If length is given and is negative then the sequence will stop that many elements from the end of the array.
     *                        If it is omitted, then the sequence will have everything from offset up until the end of the array.
     * @return AbstractCollection
     */
    public function slice($offset, $length = null)
    {
        return new self(array_slice($this->values, $offset, $length));
    }

    /**
     * Execute the callback for all collection elements
     *
     * @param  callable $callable A function in the form function ($value, $index) {}
     * @return AbstractCollection
     */
    public function each(callable $callable)
    {
        foreach ($this->values as $i => $v) {
            if (false === call_user_func_array($callable, array($v, $i))) {
                break;
            }
        }

        return $this;
    }

    /**
     * Applies the callback to all collection elements
     *
     * @param  callable $callable
     * @return AbstractCollection
     */
    public function apply(callable $callable)
    {
        $this->values = array_map($callable, $this->values);

        return $this;
    }

    /**
     * Filters elements using a callback function
     *
     * @param  callable $callable
     * @return AbstractCollection
     */
    public function filter(callable $callable = null)
    {
        return new self(array_filter($this->values, $callable));
    }
}