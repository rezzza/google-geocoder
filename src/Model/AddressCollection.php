<?php

namespace Rezzza\GoogleGeocoder\Model;

use Rezzza\GoogleGeocoder\Exception\GoogleGeocodeEmptyCollectionException;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class AddressCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var Address[]
     */
    private $addresses;

    /**
     * @param Address[] $addresses
     */
    public function __construct(array $addresses = [])
    {
        $this->addresses = array_values($addresses);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->addresses);
    }

    /**
     * @return Address
     */
    public function first()
    {
        if (empty($this->addresses)) {
            throw new GoogleGeocodeEmptyCollectionException('The AddressCollection instance is empty.');
        }

        return reset($this->addresses);
    }

    /**
     * @return Address
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return Address[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->addresses, $offset, $length);
    }

    /**
     * @return bool
     */
    public function has($index)
    {
        return isset($this->addresses[$index]);
    }

    /**
     * @return Address
     * @throws \OutOfBoundsException
     */
    public function get($index)
    {
        if (!isset($this->addresses[$index])) {
            throw new \OutOfBoundsException(sprintf('The index "%s" does not exist in this collection.', $index));
        }

        return $this->addresses[$index];
    }

    /**
     * @return Address[]
     */
    public function all()
    {
        return $this->addresses;
    }
}
