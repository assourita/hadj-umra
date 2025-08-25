<?php

namespace App\Form;

use App\Entity\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du package',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('programme', TextareaType::class, [
                'label' => 'Programme détaillé',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 8]
            ])
            ->add('prixBase', MoneyType::class, [
                'label' => 'Prix de base',
                'currency' => 'XOF',
                'attr' => ['class' => 'form-control']
            ])
            ->add('devise', ChoiceType::class, [
                'label' => 'Devise',
                'choices' => [
                    'XOF (Franc CFA)' => 'XOF',
                    'EUR (Euro)' => 'EUR',
                    'USD (Dollar US)' => 'USD'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('dureeJours', IntegerType::class, [
                'label' => 'Durée (jours)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 15']
            ])
            ->add('villeDepart', TextType::class, [
                'label' => 'Ville de départ',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('villeArrivee', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('hotelMakkah', TextType::class, [
                'label' => 'Hôtel à Makkah',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('hotelMadinah', TextType::class, [
                'label' => 'Hôtel à Madinah',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Package actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('documentsRequis', CollectionType::class, [
                'label' => 'Documents requis',
                'entry_type' => DocumentRequisType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'attr' => ['class' => 'documents-requis-collection']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
        ]);
    }
}
