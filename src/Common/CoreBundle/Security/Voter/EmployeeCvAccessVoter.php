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

namespace Common\CoreBundle\Security\Voter;

use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Employee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class EmployeeCvAccessVoter.
 */
class EmployeeCvAccessVoter extends Voter
{
    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!($subject instanceof EmployeeCv)) {
            return false;
        }

        if (!in_array($attribute, [
            'ROLE_EMPLOYEE_CV_CREATE',
            'ROLE_EMPLOYEE_CV_EDIT',
            'ROLE_EMPLOYEE_CV_DELETE',
        ])) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param EmployeeCv     $employeeCv
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $employeeCv, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Employee) {
            return false;
        }

        if ('ROLE_EMPLOYEE_CV_CREATE' === $attribute) {
            return true;
        }

        if ('ROLE_EMPLOYEE_CV_EDIT' === $attribute) {
        }

        if ('ROLE_EMPLOYEE_CV_DELETE' === $attribute) {
        }

        return $employeeCv->getEmployee() === $user;
    }
}
