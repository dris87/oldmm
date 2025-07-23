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

namespace Common\CoreBundle\Pagination;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * Class CompositePagerfantaAdapter.
 */
class CompositePagerfantaAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter1;

    /**
     * @var AdapterInterface
     */
    private $adapter2;

    /**
     * CompositePagerfantaAdapter constructor.
     *
     * @param AdapterInterface $adapter1
     * @param AdapterInterface $adapter2
     */
    public function __construct(AdapterInterface $adapter1, AdapterInterface $adapter2)
    {
        $this->adapter1 = $adapter1;
        $this->adapter2 = $adapter2;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->adapter1->getNbResults() + $this->adapter2->getNbResults();
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return array|int|\Traversable
     */
    public function getSlice($offset, $length)
    {
        $adapter1 = $this->adapter1;
        $count1 = $adapter1->getNbResults();

        if ($offset + $length <= $count1) {
            return $adapter1->getSlice($offset, $length);
        }

        $adapter2 = $this->adapter2;

        if ($count1 <= $offset) {
            return $adapter2->getSlice($offset - $count1, $length);
        }

        $length1 = $count1 - $offset;
        $result = (0 < $length1)
            ? $adapter1->getSlice($offset, $length1)
            : []
        ;

        foreach ($adapter2->getSlice(0, $length - $length1) as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
