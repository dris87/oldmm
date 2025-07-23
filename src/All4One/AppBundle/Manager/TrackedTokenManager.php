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

namespace All4One\AppBundle\Manager;

use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TrackedTokenManager.
 */
class TrackedTokenManager
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * LogManager constructor.
     *
     * @param ObjectManager $objectManager
     *
     * @throws \Exception
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TrackedToken $token
     * @param string|null  $tokenString
     *
     * @return TrackedToken
     */
    public function create(TrackedToken $token, string $tokenString = null)
    {
        if (null !== $tokenString) {
            $token->setToken($tokenString);
        }
        if (null === $token->getExpireDate()) {
            $token->setExpireDate(new \DateTime('+1 year'));
        }

        return $this->save($token, true);
    }

    /**
     * @param TrackedToken $token
     * @param bool         $skipIncrementUsedCounter
     *
     * @return TrackedToken
     */
    public function save(TrackedToken $token, bool $skipIncrementUsedCounter = false)
    {
        if (!$skipIncrementUsedCounter) {
            $token->increaseUsedCounter();
        }

        if ($token->getMaxUseTimes() === $token->getUsedCounter()) {
            $token->setStatus(TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::USED));
        }

        $this->objectManager->persist($token);
        $this->objectManager->flush();

        return $token;
    }
}
