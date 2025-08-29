<?php

namespace App\Form;

use App\Entity\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'annonce',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Nouveaux packages Hajj 2024'
                ],
                'required' => true
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de l\'annonce',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '8',
                    'placeholder' => 'Contenu détaillé de l\'annonce...'
                ],
                'required' => true
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'annonce',
                'choices' => [
                    'Général' => 'general',
                    'Hajj' => 'hajj',
                    'Umra' => 'umra',
                    'Promotion' => 'promotion',
                    'Information' => 'information',
                    'Urgent' => 'urgent'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
                'placeholder' => 'Choisir un type'
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Priorité (0-10)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 10,
                    'placeholder' => '0'
                ],
                'required' => false,
                'data' => 0
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control-file'
                ]
            ])
            ->add('isPublished', ChoiceType::class, [
                'label' => 'Statut de publication',
                'choices' => [
                    'Brouillon' => false,
                    'Publié' => true
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
                'data' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
}
