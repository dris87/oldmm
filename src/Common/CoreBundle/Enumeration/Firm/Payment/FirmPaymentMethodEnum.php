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

namespace Common\CoreBundle\Enumeration\Firm\Payment;

use Biplane\EnumBundle\Exception\InvalidEnumArgumentException;
use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class FirmPaymentMethodEnum.
 */
abstract class FirmPaymentMethodEnum extends AbstractEnum
{
    const BORGUN = 0;
    const BANK_TRANSFER = 1;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::BORGUN,
            static::BANK_TRANSFER,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::BORGUN => 'borgun',
            static::BANK_TRANSFER => 'bank_transfer',
        ];
    }

    /**
     * @param $value
     *
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|mixed|static
     */
    public static function create($value)
    {
        if (!static::isAcceptableValue($value)) {
            throw new InvalidEnumArgumentException($value);
        }

        $class = self::getReadables();
        $class = implode(array_map('ucfirst', preg_split('/[-_]/', $class[$value])));
        $class = self::class.'\\FirmPaymentMethod'.$class.'Enum';

        return new $class($value);
    }
}
