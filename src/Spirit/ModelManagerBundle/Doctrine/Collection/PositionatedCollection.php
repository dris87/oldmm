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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * FilteredCollection.
 */
class PositionatedCollection extends CollectionDecorator
{
    /**
     * @var string
     */
    private $positionFieldName;

    /**
     * @var array
     */
    private $indexes;

    /**
     * PositionatedCollection constructor.
     *
     * @param Collection $collection
     * @param $positionFieldName
     */
    public function __construct(Collection $collection, $positionFieldName)
    {
        parent::__construct($collection);

        $this->positionFieldName = $positionFieldName;

        $this->initializeIndexes();
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
        $position = $this->collection->count();
        count($this->indexes);
        $this->collection->add($element);

        return $this->collection->add($element);
    }

    /**
     * Clears the collection, removing all elements.
     */
    public function clear()
    {
        return $this->collection->clear();
    }

    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element the element to search for
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise
     */
    public function contains($element)
    {
        return $this->collection->contains($element);
    }

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise
     */
    public function isEmpty()
    {
        return $this->collection->isEmpty();
    }

    /**
     * Removes the element at the specified indexes from the collection.
     *
     * @param int|string $key the kex/indexes of the element to remove
     *
     * @return mixed the removed element or NULL, if the collection did not contain the element
     */
    public function remove($key)
    {
        return $this->collection->remove($key);
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
        return $this->collection->removeElement($element);
    }

    /**
     * Checks whether the collection contains an element with the specified key/indexes.
     *
     * @param int|string $key the key/indexes to check for
     *
     * @return bool TRUE if the collection contains an element with the specified key/indexes,
     *              FALSE otherwise
     */
    public function containsKey($key)
    {
        return $this->collection->containsKey($key);
    }

    /**
     * Gets the element at the specified key/indexes.
     *
     * @param int|string $key the key/indexes of the element to retrieve
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array the keys/indices of the collection, in the order of the corresponding
     *               elements in the collection
     */
    public function getKeys()
    {
        return $this->collection->getKeys();
    }

    /**
     * Gets all values of the collection.
     *
     * @return array the values of all elements in the collection, in the order they
     *               appear in the collection
     */
    public function getValues()
    {
        return $this->collection->getValues();
    }

    /**
     * Sets an element in the collection at the specified key/indexes.
     *
     * @param int|string $key   the key/indexes of the element to set
     * @param mixed      $value the element to set
     */
    public function set($key, $value)
    {
        return $this->collection->set($key, $value);
    }

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first()
    {
        return $this->collection->first();
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last()
    {
        return $this->collection->last();
    }

    /**
     * Gets the key/indexes of the element at the current iterator position.
     *
     * @return int|string
     */
    public function key()
    {
        return $this->collection->key();
    }

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->collection->current();
    }

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next()
    {
        return $this->collection->next();
    }

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $p the predicate
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise
     */
    public function exists(\Closure $p)
    {
        return $this->collection->exists($p);
    }

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param \Closure $p the predicate used for filtering
     *
     * @return Collection a collection with the results of the filter operation
     */
    public function filter(\Closure $p)
    {
        return $this->collection->filter($p);
    }

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p the predicate
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise
     */
    public function forAll(\Closure $p)
    {
        return $this->collection->forAll($p);
    }

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $func
     *
     * @return Collection
     */
    public function map(\Closure $func)
    {
        return $this->collection->map($func);
    }

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $p the predicate on which to partition
     *
     * @return Collection[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(\Closure $p)
    {
        return $this->collection->partition($p);
    }

    /**
     * Gets the indexes/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element the element to search for
     *
     * @return bool|int|string the key/indexes of the element or FALSE if the element was not found
     */
    public function indexesOf($element)
    {
        return $this->collection->indexesOf($element);
    }

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int      $offset the offset to start from
     * @param int|null $length the maximum number of elements to return, or null for no limit
     *
     * @return array
     */
    public function slice($offset, $length = null)
    {
        return $this->collection->slice($offset, $length);
    }

    /**
     * Count elements of an object.
     *
     * @see http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * Retrieve an external iterator.
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    /**
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->collection->offsetExists($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed can return all value types
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->collection->offsetGet($offset);
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
        return $this->collection->offsetSet($offset, $value);
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
        return $this->collection->offsetUnset($offset);
    }

    /**
     * @param string $fieldName
     * @param mixed  $model
     * @param mixed  $position
     *
     * @return string
     */
    private function setPositionValueOf($model, $position)
    {
        $setter = 'set'.implode(array_map('ucfirst', preg_split('/[_\\-]+/', $this->positionFieldName)));

        return $model->$setter($position);
    }

    /**
     * @param string $fieldName
     * @param mixed  $model
     *
     * @return string
     */
    private function getPositionValueOf($model)
    {
        $getter = 'get'.implode(array_map('ucfirst', preg_split('/[_\\-]+/', $this->positionFieldName)));

        return $model->$getter();
    }

    /**
     * @return ArrayCollection
     */
    private function createTargetCollection()
    {
        $result = new ArrayCollection();

        foreach ($this->indexes as $key => $index) {
            $result->set($key, $this->collection->get($index));
        }

        return $result;
    }

    /**
     * Initialize indexes array.
     */
    private function initializeIndexes()
    {
        if (null !== $this->indexes) {
            return;
        }

        $indexes = [];
        $isSorted = true;
        $lastPosition = null;
        $collection = $this->collection;

        foreach ($collection as $index => $model) {
            $position = $this->getPositionValueOf($model);

            $indexes[$position] = $index;

            if ($isSorted && null !== $lastPosition && $lastPosition > $position) {
                $isSorted = false;
            }
        }

        if (!$isSorted) {
            ksort($data);
        }

        $position = 1;
        foreach ($indexes as $index) {
            $this->setPositionValueOf($collection->get($index), $position);
            ++$position;
        }

        $this->indexes = array_values($indexes);
    }
}
