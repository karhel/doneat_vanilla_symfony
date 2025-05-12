<?php

namespace App\Form;

use App\Entity\BookingRequest;
use App\Entity\Meal;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingRequestCloseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('requestedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('validatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('requestComment')
            ->add('validationComment')
            ->add('closedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('requestedBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('meal', EntityType::class, [
                'class' => Meal::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookingRequest::class,
        ]);
    }
}
