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
 * Class DateRange
 */
class DateRange extends Constraint
{
    /**
     * @var string
     */
    public $from_field = 'from';
    /**
     * @var string
     */
    public $until_field = 'until';

    /**
     * @var null
     */
    public $min_days = null;

    /**
     * @var null
     */
    public $max_days = null;

    /**
     * @var string
     */
    public $message = 'validation.error.range.first-field-greater';

    /**
     * @var string
     */
    public $less_message = 'validation.error.date-range.less';

    /**
     * @var string
     */
    public $greater_message = 'validation.error.date-range.greater';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
