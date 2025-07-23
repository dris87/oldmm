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
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Class DateValueValidator.
 */
class DateValueValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!in_array($constraint->operation, ['gt', 'lt', 'gte', 'lte'])) {
            throw new ConstraintDefinitionException(
                'Operation value can be "gt", "lt", "gte", "lte".'
            );
        }

        try {
            $dateValue = new \DateTime($constraint->date_value);
        } catch (\Exception $ex) {
            throw new ConstraintDefinitionException(
                'Date value has to be a real date.'
            );
        }

        if (empty($value)) {
            return;
        }

        $this->checkValidation($value, $dateValue, $constraint);
    }

    /**
     * @param $value
     * @param Constraint $constraint
     * @param \DateTime  $dateValue
     */
    protected function checkValidation(\DateTime $value, \DateTime $dateValue, Constraint $constraint)
    {
        if (!$constraint->with_time) {
            $value->setTime(0, 0, 0, 0);
            $dateValue->setTime(0, 0, 0, 0);
        }

        $op = 'isValid'.ucfirst($constraint->operation);

        if (!$this->$op($value, $dateValue)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{value}}', $value)
                ->setParameter('{{date_value}}', $dateValue)
                ->addViolation()
            ;
        }
    }

    /**
     * @param \DateTime $value
     * @param \DateTime $dateValue
     *
     * @return bool
     */
    protected function isValidGt(\DateTime $value, \DateTime $dateValue)
    {
        return $value > $dateValue;
    }

    /**
     * @param \DateTime $value
     * @param \DateTime $dateValue
     *
     * @return bool
     */
    protected function isValidLt(\DateTime $value, \DateTime $dateValue)
    {
        return $value < $dateValue;
    }

    /**
     * @param \DateTime $value
     * @param \DateTime $dateValue
     *
     * @return bool
     */
    protected function isValidGte(\DateTime $value, \DateTime $dateValue)
    {
        return $value >= $dateValue;
    }

    /**
     * @param \DateTime $value
     * @param \DateTime $dateValue
     *
     * @return bool
     */
    protected function isValidLte(\DateTime $value, \DateTime $dateValue)
    {
        return $value <= $dateValue;
    }
}
