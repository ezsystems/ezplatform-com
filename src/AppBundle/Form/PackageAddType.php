<?php

/**
 * Form PackageAdd for adding new Package ContentType on package_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Form;

use AppBundle\Helper\PackageCategoryListHelper;
use AppBundle\Model\PackageForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageAddType extends AbstractType
{
    /** @var \AppBundle\Helper\PackageCategoryListHelper */
    private $categoryListHelper;

    /**
     * @param \AppBundle\Helper\PackageCategoryListHelper $categoryListHelper
     */
    public function __construct(PackageCategoryListHelper $categoryListHelper)
    {
        $this->categoryListHelper = $categoryListHelper;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'Packagist URL',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://packagist.org/packages/repository/bundle',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Package Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'What\'s your package name?',
                ],
            ])
            ->add('categories', ChoiceType::class, [
                'label' => 'Categories',
                'empty_data' => null,
                'multiple' => true,
                'choices' => $this->categoryListHelper->getPackageCategoryList(),
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => false,
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PackageForm::class,
        ]);
    }
}
