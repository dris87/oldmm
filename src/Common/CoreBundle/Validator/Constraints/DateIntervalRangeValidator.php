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
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class DateIntervalRangeValidator.
 */
class DateIntervalRangeValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        $minInterval = $constraint->min
            ? new \DateInterval($constraint->min)
            : null
        ;

        $maxInterval = $constraint->max
            ? new \DateInterval($constraint->max)
            : null
        ;

        if ($minInterval && $minInterval > $value) {
            $this->context->buildViolation($constraint->less_message)
                ->setParameter('{{value}}', $value)
                ->setParameter('{{min}}', $constraint->min)
                ->addViolation()
            ;
        }

        if ($maxInterval && $maxInterval < $value) {
            $this->context->buildViolation($constraint->greater_message)
                ->setParameter('{{value}}', $value)
                ->setParameter('{{max}}', $constraint->max)
                ->addViolation()
            ;
        }
    }
}
