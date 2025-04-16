<?php

namespace App\Form;

use App\Entity\Offer;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('content')
            ->add('recruiter', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
            ])
            ->add('cv', FileType::class, [
            'label' => 'Upload File (Optional)', // Customize your label
            'required' => false, // Adjust if the file is mandatory
            'mapped' => false, // Important! This tells Symfony not to map this field to a property in your entity
            'constraints' => [
                new File([
                    'maxSize' => '4092k', // Adjust the maximum file size
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                        'image/jpeg',
                        'image/png',
                        // Add more mime types as needed
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF or image file', // Customize your error message
                ])
            ],
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }
}
