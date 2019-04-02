<?php

/**
 * Form OrderType for sorting Package ContentType on package_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PackageSearchType.
 */
class PackageSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction('/packages/search/')
            ->setMethod('POST')
            ->add('search', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search Package...',
                ],
            ]);
    }
}
