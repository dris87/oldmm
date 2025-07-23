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

namespace BackOffice\AppBundle\Admin\User;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Common\CoreBundle\Entity\Employee\Employee;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class EmployeeAdmin.
 */
class UserAdmin extends AbstractAdmin
{
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
            ->add('lastLoginTime', 'doctrine_orm_datetime_range', [
                'label' => 'Utolsó aktivitás',
            ], 'sonata_type_datetime_range_picker')
            ->add('status', 'doctrine_orm_string', [
                'label' => 'Státusz',
            ], 'choice', [
                'choices' => Employee::getStatusListReverse(),
            ])
            ->add('phoneNumber', 'doctrine_orm_string', [
                'label' => 'Telefonszám',
            ])
            ->add('email', 'doctrine_orm_string', [
                'label' => 'Email',
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
            ->add('lastLoginTime', null, [
                'label' => 'Utolsó Aktivitás',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
        ;
    }
}
