<?php

namespace App\Form\Trick;

use App\Entity\Trick;
use App\Form\Trick\DTO\TrickDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('description', TextareaType::class,  [
                'required'=> true,
            ])
            ->add('title', TextType::class, [
                'required'=> true,
            ])
            ->add('pictureFiles', FileType::class, [
                'required'=>true,
                'multiple'=>true,
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TrickDTO::class,
            'translation_domain'=>'forms',
        ]);
    }


}
