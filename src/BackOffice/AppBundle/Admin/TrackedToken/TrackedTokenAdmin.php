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

namespace BackOffice\AppBundle\Admin\TrackedToken;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\User\User;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

/**
 * Class NewsPostAdmin.
 */
class TrackedTokenAdmin extends AbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('token', 'text', [
                'label' => 'Token',
            ])
            ->add('expireDate', null, [
                'label' => 'Lejárati Dátum',
            ])
            ->add('usedCounter', null, [
                'label' => 'Használatok száma',
            ])
            ->add('maxUseTimes', null, [
                'label' => 'Maximális Használatok száma',
            ])
            ->add('user', ModelAutocompleteType::class, [
                'property' => 'fullName',
                'class' => User::class,
                'label' => 'Felhasználó',
                'minimum_input_length' => 0,
                'multiple' => false,
            ], [
                'admin_code' => 'admin.user',
            ])
            ->add('offer', ModelAutocompleteType::class, [
                'property' => 'title',
                'class' => Offer::class,
                'label' => 'Hirdetés',
                'minimum_input_length' => 0,
                'multiple' => false,
            ], [
                'admin_code' => 'admin.offer',
            ])
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => TrackedTokenStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('type', EnumType::class, [
                'label' => 'label.type',
                'enum_class' => TrackedTokenTypeEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
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
            ->add(
                'token', null, [
                'label' => 'Token',
            ])
            ->add('status', null, [
                'label' => 'Státusz',
            ], 'choice', [
                'choices' => array_flip(TrackedTokenStatusEnum::getReadables()),
            ])
            ->add('type', null, [
                'label' => 'Tipus',
            ], 'choice', [
                'choices' => array_flip(TrackedTokenTypeEnum::getReadables()),
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
            ->addIdentifier('token', 'text', [
                'label' => 'Token',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->addIdentifier('user', null, [
                'label' => 'Kapcsolódó felhasználó',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'row_align' => 'center',
                'sort_field_mapping' => ['fieldName' => 'user'],
            ])
            ->addIdentifier('offer', null, [
                'label' => 'Kapcsolódó hirdetés',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'row_align' => 'center',
                'sort_field_mapping' => ['fieldName' => 'offer'],
            ])
            ->addIdentifier('usedCounter', null, [
                'label' => 'Használatok száma',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'row_align' => 'center',
                'sort_field_mapping' => ['fieldName' => 'usedCounter'],
            ])
            ->addIdentifier('maxUseTimes', null, [
                'label' => 'Maximális Használatok száma',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'row_align' => 'center',
                'sort_field_mapping' => ['fieldName' => 'maxUseTimes'],
            ])
            ->add('expireDate', null, [
                'label' => 'Lejárat ideje',
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
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('type', 'trans', [
                'label' => 'Tipus',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
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
            ])
        ;
    }
}
