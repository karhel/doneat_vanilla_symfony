<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\User;
use App\Entity\BookingRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BookingRequestValidateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('validationComment')

            ->add('refuse', SubmitType::class, [
                'label' => "Refuser la réservation"
            ])
            
            ->add('validate', SubmitType::class, [
                'label' => "Confirmer la réservation"
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
