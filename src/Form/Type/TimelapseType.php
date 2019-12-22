<?php 

namespace App\Form\Type;

use App\Entity\Timelapse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TimelapseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $resolOpts = ['384x288' => '384x288', '640x480' => '640x480', '1920x1080' => '1920x1080',];
        $extensions = ['PNG' => 'PNG', 'JPEG' => 'JPEG', 'JPG' => 'JPG', 'MJPEG' => 'MJPEG',];

        $builder
        ->add('resolution', ChoiceType::class, [
            'choices' => $resolOpts,
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ]
        ])
        ->add('fileExtension', ChoiceType::class, [
            'choices' => $extensions,
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ]
        ])
        ->add('path', TextType::class, [
            'required' => true,
            'label' => 'Local Path',
            'attr' => [
                'placeholder' => '/tmp/timelapse',
                'class' => 'form-control',
                'pattern' => '^(\/[a-zA-Z0-9_]([a-zA-Z0-9-_ ]*))*$',
            ]
        ])
        ->add('schedule', TextType::class, [
            'required' => true,
            'attr' => [
                'placeholder' => 'should looks like */15 * * * *',
                'class' => 'form-control',
                'pattern' => '^(\d{1,2} ){1,2}(\* ){2,3}|((\*\/\d{1,2} \d{1,2} |\*\/\d{1,2} |(\d{1,2} ){2})(\* ){3,4})|(?:\* ){4,5}|\@(reboot|weekly|yearly|annually|monthly|daily|hourly)$',
            ]
        ])
        ->add('save_timelapse', SubmitType::class, [
            'label' => 'Submit',
            'attr' => [
                'class' => 'btn btn-light mt-2',
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Timelapse::class,
        ]);
    }
}