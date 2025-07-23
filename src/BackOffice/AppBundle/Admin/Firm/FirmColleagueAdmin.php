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

namespace BackOffice\AppBundle\Admin\Firm;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Dictionary\DicPosition;
use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Enumeration\User\UserGenderEnum;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Class FirmColleagueAdmin.
 */
class FirmColleagueAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRouteName = 'bo_firm_colleague_admin';

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Személyes adatok', ['class' => 'col-md-6'])
            ->add('status')
            ->add('firstName')
            ->add('lastName')
            ->add('phoneNumber')
            ->add('email')
            ->end()
            ->with('Cég adatok', ['class' => 'col-md-6'])
            ->add('position')
            ->add('firm')
            ->end()
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Személyes adatok', ['class' => 'col-md-6'])
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => UserStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('firstName', 'text', [
                'label' => 'Vezetéknév',
            ])
            ->add('lastName', 'text', [
                'label' => 'Keresztnév',
            ])
            ->add('gender', EnumType::class, [
                'label' => 'label.gender',
                'enum_class' => UserGenderEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('phoneNumber', PhoneNumberType::class,
                [
                    'label' => 'label.phone_number',
                    'default_region' => 'HU',
                    'format' => PhoneNumberFormat::NATIONAL,
                    'attr' => ['placeholder' => 'placeholder.phone_number'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'attr' => ['placeholder' => 'placeholder.employee.email'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'label.password',
                    'attr' => ['placeholder' => 'placeholder.password'],
                ],
                'second_options' => [
                    'label' => 'label.password_repeat',
                    'attr' => ['placeholder' => 'placeholder.password_repeat'],
                ],
            ])
            ->end()
            ->with('Cég adatok', ['class' => 'col-md-6'])
            ->add('position', ModelAutocompleteType::class, [
                'label' => 'page.firm_colleague.registration.position',
                'minimum_input_length' => 0,
                'multiple' => false,
                'required' => true,
                'property' => 'value',
                'class' => DicPosition::class,
            ])
            ->add('firm', ModelAutocompleteType::class, [
                'property' => 'name',
                'label' => 'label.firm_name',
                'minimum_input_length' => 0,
                'required' => true,
                'to_string_callback' => function ($entity, $property) {
                    return $entity->getName();
                },
                'class' => Firm::class,
            ])
            ->end()
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'id', null, [
                'label' => 'ID',
            ])
            ->add('fullNameReverse', 'doctrine_orm_callback', [
                'callback' => function ($queryBuilder, $alias, $field, $value) {
                    if (!$value || '' == $value['value']) {
                        return;
                    }
                    $queryBuilder->andWhere('CONCAT('.$alias.'.firstName, CONCAT(\' \', '.$alias.'.lastName)) LIKE :term')->setParameter(':term', '%'.$value['value'].'%');
                    //$queryBuilder->andWhere('CONCAT('.$alias.'.lastName, CONCAT(\' \', '.$alias.'.firstName)) LIKE :term')->setParameter(':term', '%'.$value['value'].'%');

                    return true;
                },
                'field_type' => 'text',
                'label' => 'Név',
            ])
            ->add('status', 'doctrine_orm_string', [
                'label' => 'Státusz',
            ], 'choice', [
                'choices' => array_flip(UserStatusEnum::getReadables()),
            ])
            ->add('phoneNumber', 'doctrine_orm_string', [
                'label' => 'Telefonszám',
            ])
            ->add('email', 'doctrine_orm_string', [
                'label' => 'Email',
            ])
            ->add('firmname', 'doctrine_orm_callback', [
                'callback' => function (ProxyQuery $qb, $alias, $field, $value) {
                    if (!$value || '' == $value['value']) {
                        return;
                    }
                    $qb->leftJoin("Common\CoreBundle\Entity\Firm\FirmColleague", 'fc');
                    $qb->leftJoin("Common\CoreBundle\Entity\Firm\Firm", 'firm', 'WITH', 'fc.firm = firm.id');
                    $qb->andWhere('firm.name LIKE :term')->setParameter(':term', '%'.$value['value'].'%');

                    return true;
                },
                'field_type' => 'text',
                'label' => 'Cég neve',
            ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('fullNameReverse', null, [
                'label' => 'Munkatárs neve',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/firm_colleague_link_list.html.twig',
                'route' => ['name' => 'show'],
                'header_style' => 'text-align: center',
            ])
            ->add('firm.name', null, [
                'label' => 'Cég neve',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/firm_link_list.html.twig',
                'header_style' => 'text-align: center',
            ])
            ->add('position.value', null, [
                'label' => 'Beosztás',
                'sortable' => true,
                'header_style' => 'text-align: center',
            ])
            ->add('contactInfo', 'array', [
                'label' => 'Elérhetőség',
                'template' => '@SonataAdmin/CRUD/Common/contact.html.twig',
                'header_style' => 'text-align: center',
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('lastLoginTime', null, [
                'label' => 'Utolsó belépés ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('createdAt', null, [
                'label' => 'Létrehozás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('updatedAt', null, [
                'label' => 'Utolsó modósítás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('_action', null, [
                'label' => 'Opciók',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ]);
    }
}
