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

namespace BackOffice\AppBundle\Admin\BOUser;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class BOUserAdmin.
 */
class BOUserAdmin extends BaseUserAdmin
{
    /**
     * @return array
     */
    public function getExportFields()
    {
        // avoid security field to be exported
        return array_filter(parent::getExportFields(), function ($v) {
            return !in_array($v, ['password', 'salt']);
        });
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        if ($this->isCurrentRoute('edit')) {
            $formMapper
                ->with('Alapadatok', [
                    'class' => 'col-md-4, col-lg-4',
                ])
                ->add('username', TextType::class, [
                    'label' => 'Felhasználónév',
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'options' => ['translation_domain' => 'FOSUserBundle'],
                    'first_options' => ['label' => 'Új jelszó', 'required' => false],
                    'second_options' => ['label' => 'Új jelszó mégegyszer', 'required' => false],
                    'invalid_message' => 'A jelszavak nem egyeznek.',
                    'required' => false,
                ])
                ->add('fullName', TextType::class, [
                    'label' => 'Teljes név',
                ])
                ->add('emergencyContact', TextType::class, [
                    'label' => 'Probléma estén értesítendő neve',
                ])
                ->add('emergencyContactPhone', TextType::class, [
                    'label' => 'Probléma esetén értesítendő telefonszáma',
                ])
                ->add('birthDate', BirthdayType::class, [
                    'label' => 'Születési dátum',
                    'widget' => 'choice',
                ])
                ->end()
            ;
        } else {
            $formMapper
                ->with('Alapadatok', [
                    'class' => 'col-md-4, col-lg-4',
                ])
                ->add('username', TextType::class, [
                    'label' => 'Felhasználónév',
                ])
                ->add('fullName', TextType::class, [
                    'label' => 'Teljes név',
                ])
                ->add('emergencyContact', TextType::class, [
                    'label' => 'Probléma estén értesítendő neve',
                ])
                ->add('emergencyContactPhone', TextType::class, [
                    'label' => 'Probléma esetén értesítendő telefonszáma',
                ])
                ->add('birthDate', BirthdayType::class, [
                    'label' => 'Születési dátum',
                    'widget' => 'choice',
                ])
                ->end()
            ;
        }

        $formMapper

            ->with('Szakmai adatok', [
                'class' => 'col-md-4, col-lg-4',
            ])
            ->add('post', TextType::class, [
                'label' => 'Pozíció',
            ])
            ->add('superior', EntityType::class, [
                'class' => 'Application\Sonata\UserBundle\Entity\User',
                'label' => 'Felettes',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefonszám',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email cím',
            ])
            ->add('office', TextType::class, [
                'label' => 'Kirendeltség',
            ])
            ->end()

            ->with('Jogosultságok', [
                'class' => 'col-md-4, col-lg-4',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Engedélyezve',
            ])
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('fullName', null, [
                'label' => 'Név',
            ])
//            ->add('firstname', null, [
//                'label' => 'Keresztnév',
//            ])

//            ->add('post', null, array(
//                'label' => 'Pozíció'
//            ))
            ->add('phone', null, [
                'label' => 'Telefonszám',
            ])
            ->add('enabled', null, [
                'label' => 'Státusz',
            ])
//            ->add('superior', null, array(
//                'label' => 'Felettes'
//            ))
//            ->add('office', null, array(
//                'label' => 'Kirendeltség'
//            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper

            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('nameAndOffice', null, [
                'label' => "Neve\n(kirendeltsége)",
                'template' => '@SonataAdmin/CRUD/BOUserList/list__name_and_office.html.twig',
            ])

            ->add('post', null, [
                'label' => 'Pozíció',
            ])
            ->add('emailAndPhone', null, [
                'label' => 'Elérhetőségei',
                'template' => '@SonataAdmin/CRUD/BOUserList/list__email_and_phone.html.twig',
            ])
            ->add('superiorAndPost', null, [
                'label' => 'Felettes',
                'template' => '@SonataAdmin/CRUD/BOUserList/list__superior.html.twig',
            ])
            ->add('enabled', null, [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/BOUserList/list__user_status.html.twig',
            ])
            ->add('_action', 'actions', [
                'actions' => [
//                    'show' => array(),
                    'edit' => [],
                    'reset_password' => [
                        'template' => '@SonataAdmin/CRUD/BOUserList/list__action_reset_password.html.twig',
                    ],
                ],
                'label' => 'Opciók',
            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('reset_password', $this->getRouterIdParameter().'/reset_password');
    }
}
