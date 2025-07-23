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

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CvExtraType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lifeStyles', AutocompleteType::class, [
                'label' => 'label.life_style',
                'descriptor' => 'dic_life_style.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'placeholder.life_style',
            ])
            ->add('companyHelps', AutocompleteType::class, [
                'label' => 'label.company_help',
                'descriptor' => 'dic_company_help.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'placeholder.company_help',
            ])->add('extraComment', null, [
                'label' => 'label.extra_comment',
                'attr' => ['placeholder' => 'placeholder.extra_comment'],
            ])->add('salaryFrom', MoneyType::class, [
                'label' => 'label.salary_from',
                'currency' => 'huf',
                'attr' => ['placeholder' => 'placeholder.salary_from'],
            ])->add('salaryTo', MoneyType::class, [
                'label' => 'label.salary_to',
                'currency' => 'huf',
                'attr' => ['placeholder' => 'placeholder.salary_to'],
            ])->add('supports', AutocompleteType::class, [
                'label' => 'label.support',
                'descriptor' => 'dic_support.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'placeholder.support',
            ])->add('cafeteria', CheckboxType::class, [
                'label' => 'label.cafeteria',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeCv::class,
            'validation_groups' => ['extra'],
            'csrf_protection' => false,
        ]);
    }
}
