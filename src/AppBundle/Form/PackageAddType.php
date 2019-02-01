<?php

/**
 * Form PackageAdd for adding new Package ContentType on package_list.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Form;

use AppBundle\Validator\Constraints\PackageCategoryIdConstraint;
use AppBundle\Validator\Constraints\PackageDbNotExistsConstraint;
use AppBundle\Validator\Constraints\PackagistUrlConstraint;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Class PackageAddType
 *
 * @package AppBundle\Form
 */
class PackageAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = isset($options['data']['package_categories']) ? $this->getCategories($options['data']['package_categories']) : [];
        $packageListLocationId = isset($options['data']['packageListLocationId']) ? $options['data']['packageListLocationId'] : null;

        $builder
            ->add('url', UrlType::class, [
                'label' => 'Packagist URL',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://packagist.org/packages/repository/bundle',
                ],
                'constraints' => [
                    new NotNull(),
                    new Url(),
                    new PackageDbNotExistsConstraint([
                        'packageListLocationId' => $packageListLocationId,
                        'targetField' => 'packagist_url'
                    ]),
                    new PackagistUrlConstraint()
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Package Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'What\'s your package name?',
                ],
                'constraints' => [
                    new NotNull(),
                    new PackageDbNotExistsConstraint([
                        'packageListLocationId' => $packageListLocationId,
                        'targetField' => 'name'
                    ]),
                ]
            ])
            ->add('categories', ChoiceType::class, [
                'label' => 'Categories',
                'empty_data' => null,
                'multiple' => true,
                'choices' => $categories,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => false
                ],
                'constraints' => [
                    new PackageCategoryIdConstraint(['categories' => array_values($categories)])
                ]
            ]);
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    private function getCategories(array $categories): array
    {
        $choices = [];

        /** @var Tag $category */
        foreach ($categories as $category) {
            $choices[$category->getKeyword()] = $category->id;
        }

        return $choices;
    }
}
