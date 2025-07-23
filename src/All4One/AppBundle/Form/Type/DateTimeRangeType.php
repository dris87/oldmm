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

namespace All4One\AppBundle\Form\Type;

use All4One\AppBundle\Form\DataTransformer\DateTimeRangeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeRangeType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_type' => DateTimePickerType::class,
            'field_options' => [],
            'from_options' => [
                'label' => 'label.from',
            ],
            'until_options' => [
                'label' => 'label.until',
            ],
            'by_reference' => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['from_options'] = array_merge($options['field_options'], $options['from_options']);
        $options['until_options'] = array_merge($options['field_options'], $options['until_options']);
        $builder->add('from', $options['field_type'], $options['from_options']);
        $builder->add('until', $options['field_type'], $options['until_options']);

        $builder->addModelTransformer(new DateTimeRangeTransformer());
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return 'date_time_range';
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return FormType::class;
    }
}
