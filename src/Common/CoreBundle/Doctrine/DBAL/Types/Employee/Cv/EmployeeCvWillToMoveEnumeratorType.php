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

namespace Common\CoreBundle\Doctrine\DBAL\Types\Employee\Cv;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToMoveEnum;

/**
 * Class EmployeeCvWillToMoveEnumeratorType.
 */
class EmployeeCvWillToMoveEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'employee_cv_will_to_move_enum';
    }

    /**
     * @param int $value
     *
     * @return EmployeeCvWillToMoveEnum
     */
    protected function createEntity($value)
    {
        return EmployeeCvWillToMoveEnum::create($value);
    }
}
