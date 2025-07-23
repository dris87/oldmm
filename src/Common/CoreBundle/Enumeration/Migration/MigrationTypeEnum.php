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

namespace Common\CoreBundle\Enumeration\Migration;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class MigrationTypeEnum.
 */
class MigrationTypeEnum extends AbstractEnum
{
    const JSON = 0;
    const XML = 1;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::JSON,
            static::XML,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::JSON => 'JSON',
            static::XML => 'XML',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::JSON => 'success',
            static::XML => 'info',
        ];
    }
}
