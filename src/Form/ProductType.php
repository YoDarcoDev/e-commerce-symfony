<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\DataTransformer\CentimesTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('name', TextType::class, [
                 'label' => 'Nom du produit',
                 'attr' => ['placeholder' => 'Saisir le nom du produit'],
                 'required' => false
             ])
             ->add('shortDescription', TextareaType::class, [
                 'label' => 'Description courte',
                 'attr' => ['placeholder' => 'Saisir une description du produit'],
                 'required' => false
             ])

             ->add('price', MoneyType::class, [
                 'label' => 'Prix du produit ',
                 'attr' => ['placeholder' => 'Saisir le prix du produit en €'],
                 'required' => false
             ])

             ->add('mainPicture', UrlType::class, [
                 'label' => 'Image',
                 'attr' => ['placeholder' => 'Saisir une url d\'image'],
                 'required' => false
             ])

             ->add('category', EntityType::class, [
                 'label' => 'Catégorie',
                 'attr' => ['class' => 'form-control'],
                 'placeholder' => '--- Choisir une catégorie ---',
                 'class' => Category::class,
                 'choice_label' => 'name'
             ]);


        // Gestion de la transformation des prix
        $builder->get('price')->addModelTransformer(new CentimesTransformer);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
