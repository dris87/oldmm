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

namespace All4One\AppBundle\Menu;

use Common\CoreBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MenuBuilder.
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var User
     */
    private $user;

    /**
     * MenuBuilder constructor.
     *
     * @param FactoryInterface      $factory
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(FactoryInterface $factory, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoggedLeftSideMenu(array $options)
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'm-main-menu--left',
            ],
        ]);

        if (is_object($this->user)) {
            if ($this->user->isEmployee()) {
                $menu->addChild('nav.logged.employee.left.offer_list', ['route' => 'list_offers']);
                $menu->addChild('nav.logged.employee.left.cv_list', ['route' => 'employee_cv_list']);
                $menu->addChild('nav.logged.employee.left.offer_applies_list', ['route' => 'employee_offer_applies']);
            } elseif ($this->user->isFirmColleague()) {
                $menu->addChild('nav.logged.firm.left.details_header', ['route' => 'firm_details_index'])->setAttributes(['class' => 'm-main-menu--dropdown'])->setExtra('safe_label', true);
                //$menu['nav.logged.firm.left.details_header']->addChild('nav.logged.firm.left.offers.list', ['route' => 'firm_offer_list']);
                //$menu['nav.logged.firm.left.details_header']->addChild('nav.logged.firm.left.balance', ['route' => 'firm_balance']);
                $menu['nav.logged.firm.left.details_header']->addChild('nav.logged.firm.left.details', ['route' => 'firm_details_index']);
                //$menu['nav.logged.firm.left.details_header']->addChild('nav.logged.firm.left.cvs.list', ['route' => 'firm_cvs_list']);
                //$menu['nav.logged.firm.left.details_header']->addChild('nav.logged.firm.left.database_accesss', ['route' => 'firm_database_access']);
                //$menu->addChild('label.offer.new', ['route' => 'firm_offer_new']);
                //$menu->addChild('nav.logged.firm.left.services', ['route' => 'firm_services_index']);
            }
        }

        return $menu;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoggedDropdownMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        if (is_object($this->user) && $this->user->isEmployee()) {
            $menu->addChild('nav.logged.employee.dropdown.edit_personal_details', ['route' => 'employee_menage_details']);
        } elseif (is_object($this->user) && $this->user->isFirmColleague()) {
            $menu->addChild('nav.logged.firm_colleague.dropdown.edit_personal_details', ['route' => 'firm_colleague_menage_details']);
        }
        $menu->addChild('nav.logged.employee.dropdown.logout', ['route' => 'security_logout']);

        return $menu;
    }

    public function createNotLoggedRightSide(array $options)
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'main-menu--right',
            ],
        ]);

        $menu->addChild('nav.not_logged.firm_landing', ['route' => 'security_login', 'routeParameters' => ['type' => 'munkaado']])
            ->setLinkAttribute('class', 'm-main-menu--item-outline-white')
            ->setExtra('safe_label', true);
        /*$menu->addChild('nav.not_logged.employee_landing', ['route' => 'security_login', 'routeParameters' => ['type' => 'munkavallalo']])
            ->setLinkAttribute('class', 'm-main-menu--item-white')
            ->setExtra('safe_label', true);*/
        $menu->addChild('page.firm_colleague.login.label.sign_in', ['route' => 'firm_registration'])
            ->setLinkAttribute('class', 'm-main-menu--item-white')
            ->setExtra('safe_label', true);
        return $menu;
    }

    public function mobileMenu(array $options)
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => '',
            ],
        ]);

        if (is_object($this->user)) {
            if ($this->user->isEmployee()) {
                $menu->addChild('nav.logged.employee.left.offer_list', ['route' => 'list_offers']);
                $menu->addChild('nav.logged.employee.left.cv_list', ['route' => 'employee_cv_list']);
                $menu->addChild('nav.logged.employee.left.offer_applies_list', ['route' => 'employee_offer_applies']);
                $menu->addChild('nav.logged.employee.dropdown.edit_personal_details', ['route' => 'employee_menage_details']);
            } elseif ($this->user->isFirmColleague()) {
                //$menu->addChild('nav.logged.firm.left.offers.list', ['route' => 'firm_offer_list']);
                //$menu->addChild('label.offer.new', ['route' => 'firm_offer_new']);
                //$menu->addChild('nav.logged.firm.left.cvs.list', ['route' => 'firm_cvs_list']);
                //$menu->addChild('nav.logged.firm.left.database_accesss', ['route' => 'firm_database_access']);
                $menu->addChild('nav.logged.firm.left.details', ['route' => 'firm_details_index']);
                //$menu->addChild('nav.logged.firm.left.services', ['route' => 'firm_services_index']);
                //$menu->addChild('nav.logged.firm.left.balance', ['route' => 'firm_balance']);
                $menu->addChild('nav.logged.firm_colleague.dropdown.edit_personal_details', ['route' => 'firm_colleague_menage_details']);
            }
            $menu->addChild('nav.logged.employee.dropdown.logout', ['route' => 'security_logout']);
        } else {
            $menu->addChild('nav.not_logged.firm_landing', ['route' => 'security_login', 'routeParameters' => ['type' => 'munkaado']])
                ->setLinkAttribute('class', 'm-main-menu--item-outline-white')
                ->setExtra('safe_label', true);
            /*$menu->addChild('nav.not_logged.employee_landing', ['route' => 'security_login', 'routeParameters' => ['type' => 'munkavallalo']])
                ->setLinkAttribute('class', 'm-main-menu--item-white')
                ->setExtra('safe_label', true);*/
            $menu->addChild('nav.logged.employee.left.offer_list', ['route' => 'list_offers']);
            $menu->addChild('nav.not_logged.homepage', ['route' => 'homepage'])->setExtra('safe_label', true);
        }

        return $menu;
    }
}
