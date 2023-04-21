<?php

namespace App\Form;

use App\Entity\Importer;
use App\Model\UploadRequest;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UploadRequestResultDto extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('file', TextType::class)
        ->add('testOnly', CheckboxType::class, array('required' => false))
        ->add('doNotDelete', CheckboxType::class, array('required' => false))
        ->add('importer', ImporterType::class)
        ->add(
            'importer',
            EntityType::class,
            [
                'class' => Importer::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false
            ]
            );

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadRequest::class,
        ]);
    }
}

