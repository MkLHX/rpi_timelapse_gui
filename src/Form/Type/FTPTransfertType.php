<?php

namespace App\Form\Type;

use App\Entity\FTPTransfert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class FTPTransfertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('host', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => '192.168.0.1 or mydevice.example.com',
                    'class' => 'form-control',
                    'pattern' => '^([a-z.-]*|(0-9)|((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))$',
                ]
            ])
            ->add('login', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'your ftp login',
                    'class' => 'form-control',
                ]
            ])
            ->add('password', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'your ftp password',
                    'class' => 'form-control',
                ]
            ])
            ->add('path', TextType::class, [
                'required' => true,
                'label' => 'Destination Path',
                'attr' => [
                    'placeholder' => '/tmp/timelapse',
                    'class' => 'form-control',
                ]
            ])
            ->add('save_ftp', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-light mt-2',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FTPTransfert::class,
        ]);
    }
}
