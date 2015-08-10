<?php

namespace Rezzza\GoogleGeocoder\Model;

use Rezzza\GoogleGeocoder\Exception\GoogleGeocodeInvalidArgumentException;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class AdministrativeAreaLevelCollection implements \IteratorAggregate, \Countable
{
    const MAX_LEVEL_DEPTH = 5;

    /**
     * @var AdministrativeAreaLevel[]
     */
    private $administrativeAreaLevels;

    public function __construct(array $administrativeAreaLevels = [])
    {
        $this->administrativeAreaLevels = [];

        foreach ($administrativeAreaLevels as $adminLevel) {
            $level = $adminLevel->getLevel();

            $this->checkLevel($level);

            if ($this->has($level)) {
                 throw new GoogleGeocodeInvalidArgumentException(sprintf("Administrative level %d is defined twice", $level));
            }

            $this->administrativeAreaLevels[$level] = $adminLevel;
        }

        ksort($this->administrativeAreaLevels, SORT_NUMERIC);
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
        return count($this->administrativeAreaLevels);
    }

    /**
     * @return AdministrativeAreaLevel|null
     */
    public function first()
    {
        if (empty($this->administrativeAreaLevels)) {
            return null;
        }

        return reset($this->administrativeAreaLevels);
    }

    /**
     * @return AdministrativeAreaLevel[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->administrativeAreaLevels, $offset, $length, true);
    }

    /**
     * @return bool
     */
    public function has($level)
    {
        return isset($this->administrativeAreaLevels[$level]);
    }

    /**
     * @return AdministrativeAreaLevel
     * @throws \OutOfBoundsException
     * @throws GoogleGeocodeInvalidArgumentException
     */
    public function get($level)
    {
        $this->checkLevel($level);

        if (! isset($this->administrativeAreaLevels[$level])) {
            throw new GoogleGeocodeInvalidArgumentException(sprintf("Administrative level %d is not set for this address.", $level));
        }

        return  $this->administrativeAreaLevels[$level];
    }

    /**
     * @return AdministrativeAreaLevel[]
     */
    public function all()
    {
        return $this->administrativeAreaLevels;
    }

    /**
     * @param  integer               $level
     * @throws \OutOfBoundsException
     */
    private function checkLevel($level)
    {
        if ($level <= 0 || $level > self::MAX_LEVEL_DEPTH) {
            throw new \OutOfBoundsException(sprintf(
                self::MAX_LEVEL_DEPTH,
                "Administrative level should be an integer in [1,%d], %d given.",
                $level
            ));
        }
    }
}
