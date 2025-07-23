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
 * Class TrackedTokenStatusEnum.
 */
class TrackedTokenStatusEnum extends AbstractEnum
{
    const USED = 0;
    const ACTIVE = 1;
    const IN_USE = 2;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::USED,
            static::ACTIVE,
            static::IN_USE,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::USED => 'label.tracked.token.used',
            static::ACTIVE => 'label.tracked.token.active',
            static::IN_USE => 'label.tracked.token.in_use',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::USED => 'info',
            static::ACTIVE => 'success',
            static::IN_USE => 'danger',
        ];
    }
}
