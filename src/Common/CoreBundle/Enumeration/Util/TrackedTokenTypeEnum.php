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
 * Class TrackedTokenTypeEnum.
 */
class TrackedTokenTypeEnum extends AbstractEnum
{
    const FIRM_REGISTRATION = 0;
    const FIRM_ACTIVATION = 1;
    const FIRM_ACCOUNT_DELETE = 2;
    const EMPLOYEE_REGISTRATION = 3;
    const EMPLOYEE_ACTIVATION = 4;
    const EMPLOYEE_ACCOUNT_DELETE = 5;
    const RESET_PASSWORD = 6;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::FIRM_REGISTRATION,
            static::FIRM_ACTIVATION,
            static::FIRM_ACCOUNT_DELETE,
            static::EMPLOYEE_REGISTRATION,
            static::EMPLOYEE_ACTIVATION,
            static::EMPLOYEE_ACCOUNT_DELETE,
            static::RESET_PASSWORD,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::FIRM_REGISTRATION => 'label.tracked.token.firm.registration',
            static::FIRM_ACTIVATION => 'label.tracked.token.firm.activation',
            static::FIRM_ACCOUNT_DELETE => 'label.tracked.token.firm.account.delete',
            static::EMPLOYEE_REGISTRATION => 'label.tracked.token.employee.registration',
            static::EMPLOYEE_ACTIVATION => 'label.tracked.token.employee.activation',
            static::EMPLOYEE_ACCOUNT_DELETE => 'label.tracked.token.employee.account.delete',
            static::RESET_PASSWORD => 'label.tracked.token.reset.password',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::FIRM_REGISTRATION => 'success',
            static::FIRM_ACTIVATION => 'success',
            static::FIRM_ACCOUNT_DELETE => 'success',
            static::EMPLOYEE_REGISTRATION => 'danger',
            static::EMPLOYEE_ACTIVATION => 'danger',
            static::EMPLOYEE_ACCOUNT_DELETE => 'danger',
            static::RESET_PASSWORD => 'primary',
        ];
    }
}
