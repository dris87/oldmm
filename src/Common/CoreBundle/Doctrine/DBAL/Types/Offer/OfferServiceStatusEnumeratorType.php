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

namespace Common\CoreBundle\Doctrine\DBAL\Types\Offer;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\Offer\OfferServiceStatusEnum;

/**
 * Class OfferServiceStatusEnumeratorType.
 */
class OfferServiceStatusEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'offer_service_status_enum';
    }

    /**
     * @param int $value
     *
     * @return OfferServiceStatusEnum
     */
    protected function createEntity($value)
    {
        return OfferServiceStatusEnum::create($value);
    }
}
