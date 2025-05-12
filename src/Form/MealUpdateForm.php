<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\MealTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class MealUpdateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')

            ->add('imageFile', FileType::class, [
                'label' => "Envoyer une photo",
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k'
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
