<?php

/**
 * Form OrderType for sorting Bundle ContentType on Bundle_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BundleOrderType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', ChoiceType::class, array(
                'placeholder' => 'Sort by',
                'choices' => array(
                    'latestUpdate' => 'Latest Update',
                    'stars' => 'Popularity',
                    'downloads' => 'Downloads'
                ),
                'label' => false,
                'attr' => array(
                    'class' => 'form-control'
                )
            ));
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'id' => 'sort-order'
            )
        ));
    }
}