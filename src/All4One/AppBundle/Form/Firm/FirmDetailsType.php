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

namespace All4One\AppBundle\Form\Firm;

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Firm\Firm;
use Presta\ImageBundle\Form\Type\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FirmDetailsType.
 */
class FirmDetailsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ])->add('location', AutocompleteType::class, [
                'label' => 'page.firm.registration.label.location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.firm.location',
            ])
            ->add('street', null, [
                'label' => 'label.street',
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->add('postalLocation', AutocompleteType::class, [
                'label' => 'page.firm.registration.label.location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.firm.location',
            ])
            ->add('postalStreet', null, [
                'label' => 'label.street',
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->add('sitesLocation', AutocompleteType::class, [
                'label' => 'page.firm.registration.label.sites_location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.firm.location',
            ])
            ->add('sitesStreet', null, [
                'label' => 'label.street',
                'attr' => ['placeholder' => 'placeholder.street'],
            ])
            ->add('webPageUrl', null, [
                'label' => 'label.web_page_url',
                'attr' => ['placeholder' => 'placeholder.web_page_url'],
            ])
            ->add('socialMedia', null, [
                'label' => 'label.social_media',
                'attr' => ['placeholder' => 'placeholder.social_media'],
            ])
            ->add('numberOfEmployees', null, [
                'label' => 'label.number_of_employees',
                'attr' => ['placeholder' => 'placeholder.number_of_employees'],
            ])
            ->add('foundedYear', null, [
                'label' => 'label.founded_year',
                'attr' => ['placeholder' => 'placeholder.founded_year'],
            ])
            ->add('introduction', TextareaType::class, [
                'label' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.introduction'],
            ])
            ->add('visio', TextareaType::class, [
                'label' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.visio'],
            ])
            ->add('logoFile', ImageType::class, [
                'label' => false,
                'cropper_options' => [
                    'aspectRatio' => '1',
                    'viewMode' => '0',
                ],
                'upload_mimetype' => 'image/jpg',
                'upload_button_icon' => 'fa fa-cloud-upload',
                'enable_remote' => false,
                'aspect_ratios' => [['value' => '1', 'checked' => true]],
                'max_width' => '400',
                'max_height' => '400',
                'preview_width' => '130',
                'preview_height' => '130',
            ])
            ->add('coverImageFile', ImageType::class, [
                'label' => false,
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
                'enable_remote' => false,
                'aspect_ratios' => [['value' => '900/300', 'checked' => true]],
                'max_width' => '900',
                'max_height' => '300',
                'preview_height' => '130',
            ])
            ->add('whyDescription', TextareaType::class, [
                'label' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'placeholder.firm.details.why_description'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Firm::class,
            'csrf_protection' => false,
        ]);
    }
}
