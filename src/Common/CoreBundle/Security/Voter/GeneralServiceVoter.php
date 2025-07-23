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

use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Description of GeneralServiceVoter.
 *
 * @author sipee
 */
class GeneralServiceVoter extends Voter
{
    /**
     * @var FirmBalanceManager
     */
    private $balanceManager;

    /**
     * GeneralServiceVoter constructor.
     *
     * @param FirmBalanceManager $balanceManager
     */
    public function __construct(FirmBalanceManager $balanceManager)
    {
        $this->balanceManager = $balanceManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [
            'ROLE_CV_COUNT',
            'ROLE_MATCH',
            'ROLE_DATABASE',
            'ROLE_HR_MANAGEMENT',
            'ROLE_MARKETING',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $balanceManager = $this->balanceManager;
        $attribute = preg_replace('/^ROLE\\_/', '', $attribute);
        $attribute = strtolower($attribute);

        $serviceCount = $balanceManager->getServiceCount($attribute);

        return 0 < $serviceCount;
    }
}
