<?php

namespace App\Form;

use App\Model\FileImportResultDto;
use App\Model\ResourceImportResultDto;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class FileImportResultDtoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('request', UploadRequestType::class, ['disabled' => true])
        ->add('isError', CheckboxType::class, ['disabled' => true])
        ->add('error', TextType::class, ['disabled' => true])
        ->add('deleted', IntegerType::class, ['disabled' => true])
        ->add('inserted', IntegerType::class, ['disabled' => true])
        ->add('content', CollectionType::class, [
            'entry_type' => ResourceImportResultDtoType::class,
            'disabled' => true
        ]);

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FileImportResultDto::class,
        ]);
    }
}

