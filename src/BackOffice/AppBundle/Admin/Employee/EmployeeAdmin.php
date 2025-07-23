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

namespace BackOffice\AppBundle\Admin\Employee;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\DicNationality;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Enumeration\User\UserGenderEnum;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Class EmployeeAdmin.
 */
class EmployeeAdmin extends AbstractAdmin
{
    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Személyes adatok', ['class' => 'col-md-12'])
            ->add('firstName')
            ->add('lastName')
            ->add('gender')
            ->add('birthDate')
            ->add('phoneNumber')
            ->add('nationality')
            ->add('location')
            ->add('email')
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
            ->add('firstName', 'text', [
                'label' => 'Vezetéknév',
            ])
            ->add('lastName', 'text', [
                'label' => 'Keresztnév',
            ])
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => UserStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('gender', EnumType::class, [
                'label' => 'label.gender',
                'enum_class' => UserGenderEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('birthDate', BirthdayType::class, [
                'years' => range(date('Y') - 12, date('Y') - 100),
                'label' => 'label.birth_date',
                'attr' => [
                    'class' => 'enable-select2',
                ],
                'format' => 'yyyyMMMMdd',
                'required' => true,
                'input' => 'datetime',
                'placeholder' => [
                    'year' => 'placeholder.birth_year',
                    'month' => 'placeholder.birth_month',
                    'day' => 'placeholder.birth_day',
                ],
            ])
            ->add('phoneNumber', PhoneNumberType::class,
                [
                    'label' => 'label.phone_number',
                    'default_region' => 'HU',
                    'format' => PhoneNumberFormat::NATIONAL,
                    'attr' => ['placeholder' => 'placeholder.phone_number'],
            ])
            ->add('nationality', ModelAutocompleteType::class, [
                'label' => 'label.nationality',
                'minimum_input_length' => 0,
                'multiple' => true,
                'required' => true,
                'property' => 'value',
                'class' => DicNationality::class,
            ], [
                'admin_code' => 'admin.dic_nationality',
            ])
            ->add('location', ModelAutocompleteType::class, [
                'property' => 'text',
                'class' => DicLocation::class,
                'label' => 'label.location',
                'minimum_input_length' => 0,
                'required' => true,
            ], [
                'admin_code' => 'admin.dic_full_location',
            ])
            ->end()
            ->with('Email és jelszó', ['class' => 'col-md-6'])
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
                    'label' => 'label.empty',
                    'attr' => ['placeholder' => 'placeholder.password_repeat'],
                ],
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
            
            ->add('fullName', 'doctrine_orm_callback', [
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
                'show_filter' => true,
            ])
            
            ->add('location', 'doctrine_orm_callback', [
                'callback' => function ($queryBuilder, $alias, $field, $value) {
             
                    if (!$value || '' == $value['value']) {
                        return;
                    }
                    $queryBuilder->leftJoin("Common\CoreBundle\Entity\Dictionary\DicLocation", 'loc', 'WITH', 'loc.id = '.$alias.'.locationId');
                    $queryBuilder->leftJoin("Common\CoreBundle\Entity\Dictionary\Dictionary", 'dicC', 'WITH', 'dicC.id = loc.cityId');
                    $queryBuilder->andWhere('dicC.value LIKE :term');
                    $queryBuilder->setParameters([':term' => '%'.$value['value'].'%']);
                    
                  
                    //$queryBuilder->andWhere('CONCAT('.$alias.'.lastName, CONCAT(\' \', '.$alias.'.firstName)) LIKE :term')->setParameter(':term', '%'.$value['value'].'%');

                    return true;
                },
                'field_type' => 'text',
                'label' => 'Város',
                'show_filter' => true,
            ])
            ->add('lastLoginTime', 'doctrine_orm_datetime_range', [
                'label' => 'Utolsó aktivitás',
                ], 'sonata_type_datetime_range_picker')
            ->add('status', 'doctrine_orm_string', [ 
                'show_filter' => true,
                'label' => 'Státusz',
            ], 'choice', [
                    'choices' => Employee::getStatusListReverse(),
                ])
            ->add('phoneNumber', 'doctrine_orm_string', [
                'label' => 'Telefonszám',
            ])
            ->add('email', 'doctrine_orm_string', [
                'label' => 'Email',
                'show_filter' => true,
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
                'header_style' => 'text-align: center',
                'row_align' => 'center',
                'sortable' => true,
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
            ->addIdentifier('getFullName', 'text', [
                'label' => 'Név',
                'sortable' => true,
                'route' => ['name' => 'show'],
                'sort_field_mapping' => ['fieldName' => 'firstName'],
                'sort_parent_association_mappings' => [],
            ])
            ->add('contactInfo', 'array', [
                'label' => 'Elérhetőség',
                'template' => '@SonataAdmin/CRUD/Common/contact.html.twig',
                'sortable' => false,
            ])
            ->add('lastLoginTimeForList', 'text', [
                'label' => 'Utolsó aktivitás',
                'sortable' => true,
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('cvCount', null, [
                'label' => 'CV',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [],
                'row_align' => 'center',
            ])
            ->add('_custom', null, [
                'label' => 'CV lista',
                'template' => '@SonataAdmin/CRUD/EmployeeList/employee_cv_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('_action', null, [
                'label' => 'Opciók',
                'actions' => [
                    'edit' => [],
                    'delete' => [
                        'class' => 'btn-danger',
                    ],
                ],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
        ;
    }
}
