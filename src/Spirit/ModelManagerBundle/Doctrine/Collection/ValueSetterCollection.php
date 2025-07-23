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

namespace Spirit\ModelManagerBundle\Doctrine\Collection;

use Doctrine\Common\Collections\Collection;

/**
 * ValueSetterCollection.
 */
class ValueSetterCollection extends CollectionDecorator
{
    /**
     * @var array
     */
    private $addingValues;

    /**
     * @var array
     */
    private $removingValues;

    /**
     * CollectionDecorator constructor.
     *
     * @param Collection $collection
     * @param \Closure   $predicate
     * @param array      $addingValues
     * @param array      $removingValues
     */
    public function __construct(Collection $collection, array $addingValues, array $removingValues = [])
    {
        parent::__construct($collection);

        $this->addingValues = $addingValues;
        $this->removingValues = $removingValues;
    }

    /**
     * Adds an element at the end of the collection.
     *
     * @param mixed $element the element to add
     *
     * @return bool always TRUE
     */
    public function add($element)
    {
        $this->applyAddingValuesOn($element);

        return parent::add($element);
    }

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param int|string $key   the key/index of the element to set
     * @param mixed      $value the element to set
     */
    public function set($key, $value)
    {
        $this->applyAddingValuesOn($value);

        return parent::set($key, $value);
    }

    /**
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->applyAddingValuesOn($value);

        return parent::offsetSet($offset, $value);
    }

    /**
     * Clears the collection, removing all elements.
     */
    public function clear()
    {
        foreach ($this as $element) {
            $this->applyRemovingValuesOn($element);
        }

        return parent::clear();
    }

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param int|string $key the kex/index of the element to remove
     *
     * @return mixed the removed element or NULL, if the collection did not contain the element
     */
    public function remove($key)
    {
        $object = parent::remove($key);

        if ($object) {
            $this->applyRemovingValuesOn($object);
        }

        return $object;
    }

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element the element to remove
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeElement($element)
    {
        $result = parent::removeElement($element);

        if ($result) {
            $this->applyRemovingValuesOn($element);
        }

        return $result;
    }

    /**
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @param object $element
     */
    private function applyAddingValuesOn($element)
    {
        $this->applyValuesOn($element, $this->addingValues);
    }

    /**
     * @param object $element
     */
    private function applyRemovingValuesOn($element)
    {
        $this->applyValuesOn($element, $this->removingValues);
    }

    /**
     * @param object $element
     * @param array  $values
     */
    private function applyValuesOn($element, array $values)
    {
        foreach ($values as $path => $value) {
            $this->setValuesByPath($element, $path, $value);
        }
    }

    /**
     * @param object $element
     * @param string $path
     * @param mixed  $value
     */
    private function setValuesByPath($element, $path, $value)
    {
        $path = explode('.', $path);
        $count = count($path);

        for ($i = 0; $i < $count - 1; ++$i) {
            $getter = $this->getterNameOf($path[$i]);

            $element = $element->$getter();
        }

        $setter = $this->setterNameOf($path[$i]);

        $element->$setter($value);
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    private function setterNameOf($fieldName)
    {
        return 'set'.implode(array_map('ucfirst', preg_split('/[_\\-]+/', $fieldName)));
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    private function getterNameOf($fieldName)
    {
        return 'get'.implode(array_map('ucfirst', preg_split('/[_\\-]+/', $fieldName)));
    }
}
