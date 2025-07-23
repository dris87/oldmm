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
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Enumeration\Firm\FirmStatusEnum;
use Presta\ImageBundle\Form\Type\ImageType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class FirmAdmin.
 */
class FirmAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRouteName = 'bo_firm_admin';

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->tab('Általános')
            ->with('Cég infó', ['class' => 'col-md-6'])
            ->add('status')
            ->add('taxNumber')
            ->add('name')
            ->add('nameLong')
            ->add('representative')
            ->end()
            ->with('Cég Címei', ['class' => 'col-md-6'])
            ->add('location')
            ->add('street')
            ->add('postalLocation')
            ->add('postalStreet')
            ->add('sitesLocation')
            ->add('sitesStreet')
            ->end()
            ->end()
            ->tab('Egyéb infó')
            ->with('Egyéb infó', ['class' => 'col-md-6'])
            ->add('webPageUrl')
            ->add('socialMedia')
            ->add('numberOfEmployees')
            ->add('foundedYear')
            ->end()
            ->with('Szövegek', ['class' => 'col-md-6'])
            ->add('introduction')
            ->add('visio')
            ->add('whyDescription')
            ->end()
            ->end()
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Általános')
            ->with('Cég infó', ['class' => 'col-md-6'])
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => FirmStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('taxNumber', null, [
                'attr' => ['placeholder' => 'placeholder.tax_number'],
                'label' => 'label.tax_number',
            ])
            ->add('name', null, [
                'label' => 'page.firm.registration.label.firm_name',
                'attr' => ['placeholder' => 'placeholder.firm.name'],
            ])
            ->add('nameLong', null, [
                'label' => 'page.firm.registration.label.firm_name_long',
                'attr' => ['placeholder' => 'placeholder.firm.name_long'],
            ])
            ->add('representative', null, [
                'label' => 'page.firm.registration.label.representative',
                'attr' => ['placeholder' => 'placeholder.firm.representative'],
            ])
            ->end()
            ->with('Cég Címei', ['class' => 'col-md-6'])
            ->add('location', ModelAutocompleteType::class, [
                'property' => 'text',
                'class' => DicLocation::class,
                'to_string_callback' => function($entity, $property) {
                      return $entity->getFullLocation();
                },
                'label' => 'page.firm.registration.label.location',
                'minimum_input_length' => 0,
                'required' => true,
            ], [
                'admin_code' => 'admin.dic_full_location',
            ])
            ->add('street', null, [
                'label' => 'Utca',
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->add('postalLocation', ModelAutocompleteType::class, [
                'property' => 'text',
                'class' => DicLocation::class,
                'label' => 'Postai cím',
                'minimum_input_length' => 0,
                'required' => false,
            ], [
                'admin_code' => 'admin.dic_full_location',
            ])
            ->add('postalStreet', null, [
                'label' => 'Postai utca',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->add('sitesLocation', ModelAutocompleteType::class, [
                'property' => 'text',
                'class' => DicLocation::class,
                'label' => 'Telephely cím',
                'minimum_input_length' => 0,
                'required' => false,
            ], [
                'admin_code' => 'admin.dic_full_location',
            ])
            ->add('sitesStreet', null, [
                'label' => 'Telephely utca',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->end()
            ->end()
            ->tab('Egyéb infó')
            ->with('Egyéb infó', ['class' => 'col-md-6'])
            ->add('webPageUrl', null, [
                'label' => 'label.web_page_url',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.web_page_url'],
            ])
            ->add('socialMedia', null, [
                'label' => 'label.social_media',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.social_media'],
            ])
            ->add('numberOfEmployees', null, [
                'label' => 'label.number_of_employees',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.number_of_employees'],
            ])
            ->add('foundedYear', null, [
                'label' => 'label.founded_year',
                'required' => false,
                'attr' => ['placeholder' => 'placeholder.founded_year'],
            ])
            ->end()
            ->with('Szövegek', ['class' => 'col-md-6'])
            ->add('introduction', TextareaType::class, [
                'label' => 'Bemutatkózás',
                'required' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.introduction'],
            ])
            ->add('visio', TextareaType::class, [
                'label' => 'Vízió',
                'required' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.visio'],
            ])
            ->add('whyDescription', TextareaType::class, [
                'label' => 'Miért nálunk?',
                'required' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.why_description'],
            ])
            ->end()
            ->end()
            ->tab('Dokumentumok')
            ->with('Dokumentumok', ['class' => 'col-md-12'])
            ->add('logoFile', ImageType::class, [
                'label' => 'Logó',
                'cropper_options' => [
                    'aspectRatio' => '1',
                    'viewMode' => '0',
                ],
                'upload_mimetype' => 'image/jpg',
                'upload_button_icon' => 'fa fa-cloud-upload',
                'enable_remote' => true,
                'aspect_ratios' => [['value' => '1', 'checked' => true]],
                'max_width' => '400',
                'max_height' => '400',
                'preview_width' => '130',
                'preview_height' => '130',
            ])
            ->add('coverImageFile', ImageType::class, [
                'label' => 'Cover kép',
                'cropper_options' => [
                    'aspectRatio' => '900/300',
                    'viewMode' => '3',
                    'minCropBoxWidth' => 900,
                    'minCropBoxHeight' => 300,
                    'minContainerWidth' => 300,
                    'minContainerHeight' => 300,
                    'minCanvasWidth' => 900,
                    'minCanvasHeight' => 300,
                ],
                'upload_mimetype' => 'image/jpg',
                'upload_button_icon' => 'fa fa-cloud-upload',
                'enable_remote' => true,
                'aspect_ratios' => [['value' => '900/300', 'checked' => true]],
                'max_width' => '900',
                'max_height' => '300',
                'preview_height' => '130',
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
            ->add('name', null, [
               'label' => 'Cég neve',
            ])
            ->add('representative', null, [
                'label' => 'Cégképviseletre jogosult neve',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->addIdentifier('name', 'text', [
                'label' => 'Cég neve',
                'route' => ['name' => 'show'],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('representative', 'text', [
                'label' => 'Cégképviseletre jogosult neve',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('contactInfo', 'array', [
                'label' => 'Kapcsolattartó elérhetősége',
                'template' => '@SonataAdmin/CRUD/Common/firm_contact.html.twig',
                'sortable' => false,
            ])
            ->add('lastActivity', 'text', [
                'label' => 'Utolsó aktivitás',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
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
                'actions' => [
                    'balance_info' => [
                        'template' => 'BackOfficeAppBundle::admin/button/balance_info_button.html.twig',
                    ],
                    'purchase_list' => [
                        'template' => 'BackOfficeAppBundle::admin/button/purchase_list_button.html.twig',
                    ],
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
