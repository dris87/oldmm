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

namespace Common\CoreBundle\Enumeration\Util;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class ContactStatusEnum.
 */
class ContactStatusEnum extends AbstractEnum
{
    const NOT_ANSWERED = 0;
    const ANSWERED = 1;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::NOT_ANSWERED,
            static::ANSWERED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::NOT_ANSWERED => 'not_answered',
            static::ANSWERED => 'answered',
        ];
    }
}
