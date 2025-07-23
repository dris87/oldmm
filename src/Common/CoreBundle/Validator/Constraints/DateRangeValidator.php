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

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class DateRangeValidator.
 */
class DateRangeValidator extends ConstraintValidator
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * DateRangeValidator constructor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $from = $this->propertyAccessor->getValue($value, $constraint->from_field);
        $until = $this->propertyAccessor->getValue($value, $constraint->until_field);

        if (null === $from || null === $until) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;

            return;
        }

        if ($until < $from) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;

            return;
        }

        $days = (int) ($from->diff($until)->format('%a'));
        $minDays = (int) ($constraint->min_days);
        $maxDays = (int) ($constraint->max_days);

        if (null !== $minDays && $minDays > $days) {
            $this->context->buildViolation($constraint->less_message)
                ->setParameter('{{days}}', $days)
                ->setParameter('{{min_days}}', $minDays)
                ->addViolation()
            ;
        }

        if (null !== $maxDays && $maxDays < $days) {
            $this->context->buildViolation($constraint->greater_message)
                ->setParameter('{{days}}', $days)
                ->setParameter('{{max}}', $maxDays)
                ->addViolation()
            ;
        }
    }
}
