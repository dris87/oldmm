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

namespace Common\CoreBundle\Doctrine\DBAL\Types\Employee;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\Employee\CoverLetterStatusEnum;

/**
 * Class CoverLetterStatusEnumeratorType.
 */
class CoverLetterStatusEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'employee_cover_letter_status_enum';
    }

    /**
     * @param int $value
     *
     * @return CoverLetterStatusEnum
     */
    protected function createEntity($value)
    {
        return CoverLetterStatusEnum::create($value);
    }
}
