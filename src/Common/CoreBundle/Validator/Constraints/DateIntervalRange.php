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

namespace Common\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * Class DateIntervalRange
 */
class DateIntervalRange extends Constraint
{
    /**
     * @var null
     */
    public $min = null;

    /**
     * @var null
     */
    public $max = null;

    /**
     * @var string
     */
    public $message = 'error.date-interval-range.less';

    /**
     * @var string
     */
    public $less_message = 'error.date-interval-range.less';

    /**
     * @var string
     */
    public $greater_message = 'error.date-interval-range.greater';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
