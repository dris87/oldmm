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
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class FirmCvVoter.
 */
class FirmCvVoter extends Voter
{
    /**
     * @var FirmBalanceManager
     */
    private $balanceManager;

    /**
     * FirmCvVoter constructor.
     *
     * @param FirmBalanceManager $balanceManager
     */
    public function __construct(FirmBalanceManager $balanceManager)
    {
        $this->balanceManager = $balanceManager;
    }

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
            'ROLE_CV_UNLOCK',
            'ROLE_FIRM_CV_VIEW',
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

        if (!$user instanceof FirmColleague) {
            return false;
        }

        $firm = $user->getFirm();

        if ('ROLE_CV_UNLOCK' === $attribute) {
            // do we have enough credit?
            $cv_count = $this->balanceManager->getServiceCountByEnum(FirmPackageServiceEnum::create(FirmPackageServiceEnum::CV_COUNT));

            if ($cv_count < 1) {
                return false;
            }

            return !$firm->isCvUnlocked($employeeCv);
        }

        if ('ROLE_FIRM_CV_VIEW' === $attribute) {
            return $firm->isCvUnlocked($employeeCv);
        }

        return false;
    }
}
