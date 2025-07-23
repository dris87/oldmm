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

namespace BackOffice\AppBundle\Form\DataTransformer;

use Common\CoreBundle\Enumeration\OfferStatusEnum;
use Symfony\Component\Form\DataTransformerInterface;

class OfferStatusTypeToNumberTransformer implements DataTransformerInterface
{
    public function transform($offerStatus)
    {
        if (null === $offerStatus) {
            return null;
        }

        return $offerStatus->getValue();
    }

    public function reverseTransform($offerStatusNumber)
    {
        if (null === $offerStatusNumber || '' === $offerStatusNumber) {
            return null;
        }

        return OfferStatusEnum::createByReadable($offerStatusNumber);
    }
}
