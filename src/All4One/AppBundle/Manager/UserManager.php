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

use Common\CoreBundle\Doctrine\Repository\User\UserRepository;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Firm\DeletedFirmColleague;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\User\DeletedUser;
use Common\CoreBundle\Entity\User\User;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class UserManager.
 */
class UserManager
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * UserManager constructor.
     *
     * @param ObjectManager                $objectManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TokenStorage                 $tokenStorage
     * @param Session                      $session
     * @param EventDispatcher              $eventDispatcher
     */
    public function __construct(ObjectManager $objectManager, UserPasswordEncoderInterface $passwordEncoder, TokenStorage $tokenStorage, Session $session, EventDispatcher $eventDispatcher)
    {
        $this->objectManager = $objectManager;
        $this->userRepository = $this->objectManager->getRepository('CommonCoreBundle:User\User');
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     * @param bool $encodePassword
     * @param bool $notVerified
     *
     * @return User
     */
    public function create(User $user, bool $encodePassword = true, bool $notVerified = true)
    {
        $password = $user->getPlainPassword();
        if ($encodePassword) {
            $password = $this->passwordEncoder->encodePassword($user, $password);
        }
        $user->setPassword($password);
        $user->setRoles($this->getUserTypeRoles($user));
        if ($notVerified) {
            $user->setStatus(UserStatusEnum::create(UserStatusEnum::NOT_VERIFIED));
        }
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @throws \Exception
     *
     * @return User
     */
    public function save(User $user)
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    /**
     * @param User   $user
     * @param string $password
     */
    public function changePassword(User $user, string $password)
    {
        $password = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($password);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * @param User    $user
     * @param Request $request
     */
    public function logUserIn(User $user, Request $request)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_main', serialize($token));
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return object|null
     */
    public function getUserById(int $id)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $id]);

        return $user;
    }

    /**
     * @param string $email
     *
     * @return User|null
     */
    public function getUserByEmail(string $email)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        return $user;
    }

    /**
     * @param DeletedUser $deletedUser
     * @param User        $user
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteUser(DeletedUser $deletedUser, User $user)
    {
        $this->tokenStorage->setToken(null);
        $this->session->invalidate();

        $deletedUser->setFirstName($user->getFirstName());
        $deletedUser->setLastName($user->getLastName());
        $deletedUser->setEmail($user->getEmail());

        if ($user instanceof FirmColleague) {
            /* @var DeletedFirmColleague $deletedUser */
            // todo: Order and balance save to deletedUser!
        }

        $this->objectManager->persist($deletedUser);
        $this->objectManager->flush();
        $this->objectManager->remove($user);
        $this->objectManager->flush();

        return true;
    }

    /**
     * This method will return the default roles to set
     * for a given user type.
     *
     * @param User $user
     *
     * @return array
     */
    private function getUserTypeRoles(User $user)
    {
        if ($user instanceof FirmColleague) {
            return FirmColleague::ROLES;
        }

        return Employee::ROLES;
    }
}
