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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class DicFullLocationAdmin.
 */
class DicFullLocationAdmin extends AbstractAdmin
{
    /**
     * @param $queryBuilder
     * @param $alias
     * @param $field
     * @param $value
     */
    public function getNameFilter($queryBuilder, $alias, $field, $value)
    {
        //Keresési érték létezik
        if (!$value['value']) {
            return;
        }

        $queryBuilder->leftJoin(sprintf('%s.city', $alias), 'c');
        $queryBuilder->leftJoin(sprintf('%s.zip', $alias), 'z');

        $queryBuilder
            ->andWhere('c.value like :name OR z.value like :name')
            ->setParameter('name', '%'.$value['value'].'%');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Szótár', ['class' => 'col-md-12'])
            ->add('zip', null, [
                'attr' => ['placeholder' => 'placeholder.zip'],
                'label' => 'label.zip',
            ])
            ->add('city', null, [
                'attr' => ['placeholder' => 'placeholder.city'],
                'label' => 'label.city',
            ])
            ->add('county', null, [
                'attr' => ['placeholder' => 'placeholder.county'],
                'label' => 'label.county',
            ])
            ->end()
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('text', 'doctrine_orm_callback', [
                'callback' => [$this, 'getNameFilter'],
                'field_type' => TextType::class,
            ])
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
            ->addIdentifier('city', 'text', [
                'label' => 'Város',
                'header_style' => 'text-align: center',
            ])
            ->addIdentifier('zip', 'text', [
                'label' => 'Irányítószám',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->addIdentifier('county', 'text', [
                'label' => 'Megye',
                'header_style' => 'text-align: center',
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
