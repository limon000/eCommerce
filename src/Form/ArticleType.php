<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prix')
            ->add('categorie' , ChoiceType::class, [
                'placeholder' => 'Choose an option',
                'choices' => [
                    'Informatique' => 'Informatique',
                    'Telephonie' => 'Telephonie',
                    'Electromenager' => 'Electromenager',
                    'TV | Photo & Son' => 'TV | Photo & Son',
                    'Immobilier' => 'Immobilier',
                ]
            ])
            ->add('image', FileType::class, [
                'attr' => [
                    'full_name' => 'image'
                ]
            ])
            ->add('quantite')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
