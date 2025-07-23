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
 * Class NotificationTypeEnum.
 */
class NotificationTypeEnum extends AbstractEnum
{
    const SUCCESS = 'success';
    const DANGER = 'danger';
    const INFO = 'info';
    const WARNING = 'warning';

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::SUCCESS,
            static::DANGER,
            static::INFO,
            static::WARNING,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::SUCCESS => 'success',
            static::DANGER => 'danger',
            static::INFO => 'info',
            static::WARNING => 'warning',
        ];
    }
}
