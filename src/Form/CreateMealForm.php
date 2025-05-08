<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\MealTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Bazinga\GeocoderBundle\Validator\Constraint\Address;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CreateMealForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            
            ->add('address', null, [
                'constraints' => [
                    new Address([
                        'message' => "L'adresse saisie n'est pas reconnue",
                    ]),
                ]
            ])

            ->add('latitude',   HiddenType::class)
            ->add('longitude',  HiddenType::class)

            ->add('imageFile', FileType::class, [
                'label' => "Envoyer une photo",
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ]
                    ])
                ],
                'required' => false,
            ])

            ->add('tags', EntityType::class, [
                'class' => MealTag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
        ]);
    }
}
