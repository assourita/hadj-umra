<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Package;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('package', EntityType::class, [
                'class' => Package::class,
                'choice_label' => 'name',
                'label' => 'Package sélectionné',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@example.com'
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+33 6 12 34 56 78'
                ]
            ])
            ->add('numberOfPeople', IntegerType::class, [
                'label' => 'Nombre de personnes *',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 10
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Sexe *',
                'choices' => [
                    'Homme' => 'male',
                    'Femme' => 'female',
                    'Mixte' => 'mixed'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse *',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Votre adresse complète'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre ville'
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '75001'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'France'
                ]
            ])
            ->add('specialRequests', TextareaType::class, [
                'label' => 'Demandes spéciales',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Demandes particulières, allergies, etc.'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
