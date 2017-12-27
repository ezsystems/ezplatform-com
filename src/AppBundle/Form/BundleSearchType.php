<?php

/**
 * Form OrderType for sorting Bundle ContentType on Bundle_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BundleSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction('/Bundles/search/')
            ->setMethod('POST')
            ->add('search', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search Bundle...'
                ]
            ]);
    }
}