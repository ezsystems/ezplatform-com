<?php

/**
 * Form OrderType for sorting Package ContentType on package_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PackageOrderType extends AbstractType
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
                    'Latest Update' => 'latestUpdate',
                    'Popularity' => 'stars',
                    'Downloads' => 'downloads',
                ),
                'label' => false,
                'attr' => array(
                    'class' => 'form-control',
                ),
            ));
    }
}
