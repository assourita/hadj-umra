<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'actualité',
                'attr' => [
                    'placeholder' => 'Ex: Hajj 2024 - Inscriptions Ouvertes'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => 'Contenu détaillé de l\'actualité...'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de l\'actualité',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG ou WebP)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
            ->add('isPublished', ChoiceType::class, [
                'label' => 'Statut de publication',
                'choices' => [
                    'Publié' => true,
                    'Brouillon' => false
                ]
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Date de publication',
                'widget' => 'single_text',
                'required' => false,
                'data' => new \DateTime()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
