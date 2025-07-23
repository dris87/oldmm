<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Presentation;

/**
 * Class DateTimeRange.
 */
class DateTimeRange implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var \DateTime
     */
    private $from;

    /**
     * @var \DateTime
     */
    private $until;

    /**
     * @return \DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * @param $until
     *
     * @return $this
     */
    public function setUntil($until)
    {
        $this->until = $until;

        return $this;
    }

    /**
     * @return bool|\DateInterval|null
     */
    public function getInterval()
    {
        return ($this->from && $this->until)
            ? $this->from->diff($this->until)
            : null
        ;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return method_exists($this, 'get'.ucfirst($offset));
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return call_user_func([$this, 'get'.ucfirst($offset)]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        call_user_func([$this, 'set'.ucfirst($offset)], $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator([
            'from' => $this->getFrom(),
            'until' => $this->getUntil(),
            'interval' => $this->getInterval(),
        ]);
    }
}
