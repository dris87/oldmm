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

namespace BackOffice\AppBundle\Admin\Autocomplete;

use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Enumeration\Dictionary\DictionaryStatusEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class DicLocationAdmin.
 */
class DicLocationAdmin extends AbstractAdmin
{
    /**
     * @param string $context
     *
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->where('o INSTANCE OF Common\CoreBundle\Entity\Dictionary\DicCity OR o INSTANCE OF Common\CoreBundle\Entity\Dictionary\DicCounty');

        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('value');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Általános')
            ->with('Szótár', ['class' => 'col-md-6'])
            ->add('value', null, [
                'attr' => ['placeholder' => 'placeholder.tax_number'],
                'label' => 'label.tax_number',
            ])
            ->add('status', EnumType::class, [
                'label' => 'Státusz',
                'enum_class' => DictionaryStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->addIdentifier('value', 'text', [
                'label' => 'Érték',
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
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
                'label' => 'Opciók',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
        ;
    }
}
