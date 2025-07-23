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

namespace All4One\AppBundle\Form\Employee;

use All4One\AppBundle\Form\Type\DateTimePickerType;
use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CvEducationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', AutocompleteType::class, [
                'label' => 'label.education.category',
                'descriptor' => 'dic_category.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'placeholder' => 'placeholder.education.category',
            ])
            ->add('location', AutocompleteType::class, [
                'label' => 'label.education.location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'placeholder' => 'placeholder.education.location',
            ])
            ->add('school', AutocompleteType::class, [
                'label' => 'label.education.school',
                'descriptor' => 'dic_school.descriptor',
                'is_creation_allowed' => true,
                'minimum_input_length' => 0,
                'required' => false,
                'placeholder' => 'placeholder.education.school',
            ])
            ->add('educationLevel', AutocompleteType::class, [
                'label' => 'label.education.level',
                'descriptor' => 'dic_education_level.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'placeholder' => 'placeholder.education.level',
            ])->add('comment', null, [
                'label' => 'label.education.comment',
                'required' => false,
            ])->add('inProgress', CheckboxType::class, [
                'label' => 'label.education.in_progress',
                'required' => false,
            ])->add('fromDate', DateTimePickerType::class, [
                'label' => 'label.education.date',
                'format' => 'yyyy-MM',
                'required' => false,
                'viewMode' => 'years',
            ])->add('toDate', DateTimePickerType::class, [
                'label' => 'label.empty',
                'format' => 'yyyy-MM',
                'required' => false,
                'viewMode' => 'years',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeCvEducation::class,
            'csrf_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];

                if (true !== $form->getData()->getInProgress()) {
                    $groups[] = 'notInProgress';
                }

                return $groups;
            },
        ]);
    }
}
