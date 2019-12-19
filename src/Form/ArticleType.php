<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prix', NumberType::class)
            ->add('categorie' , ChoiceType::class, [
                'placeholder' => 'Choose an option',
                'choices' => [
                    'Informatique' => 'Informatique',
                    'Telephonie' => 'Telephonie',
                    'TV | Photo & Son' => 'TV | Photo & Son',
                    'Gaming' => 'Gaming',
                    'Impression' => 'Impression',
                    'Reseaux & Securite' => 'Reseaux & Securite',
                ]
            ])
            ->add('image', FileType::class, [
                'attr' => [
                    'full_name' => 'image'
                ],
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
            ->add('quantite', NumberType::class)
            ->add('description', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
