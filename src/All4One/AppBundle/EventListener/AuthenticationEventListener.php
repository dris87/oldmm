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

namespace All4One\AppBundle\EventListener;

use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\User\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationEventListener implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $container;
    /**
     * @var EntityManager em
     */
    protected $em;

    public function __construct(Router $router, $container)
    {
        $this->router = $router;
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $response = new Response();

        /** @var User $user */
        $user = $token->getUser();

        $redirectUrl = 'homepage';

        if ($user instanceof Employee) {
            $redirectUrl = 'list_offers';
        } elseif ($user instanceof FirmColleague) {
            $redirectUrl = 'firm_offer_index';
        }

        $user->setLastLoginTime(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();

        $response = new RedirectResponse($this->router->generate($redirectUrl));

        return $response;
    }
}
