<?php

namespace App\Controller;

use App\Entity\FTPTransfert;
use App\Entity\Timelapse;
use App\Repository\FTPTransfertRepository;
use App\Repository\TimelapseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @Route("/", name="timelapse")
 */
class TimelapseController extends AbstractController
{

    /** 
     *  on install add this in user crontab
     *  * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1
     *  mkdir /tmp/timelapse
     */

    /**
     * @Route("", name="_index")
     */
    public function index(Request $request, EntityManagerInterface $entityManager, TimelapseRepository $timelapseRepository, FTPTransfertRepository $fTPTransfertRepository)
    {
        $timelapse = $timelapseRepository->findOneBy([], ['id' => 'DESC']);
        if (null == $timelapse) {
            $timelapse = new Timelapse();
        }

        $ftpTransfert = $fTPTransfertRepository->findOneBy([], ['id' => 'DESC']);
        if (null == $ftpTransfert) {
            $ftpTransfert = new FTPTransfert();
        }

        $resolOpts = ['384x288' => '384x288', '640x480' => '640x480', '1920x1080' => '1920x1080',];
        $extensions = ['PNG' => 'PNG', 'JPEG' => 'JPEG', 'JPG' => 'JPG', 'MJPEG' => 'MJPEG',];
        $timelapseForm = $this->createFormBuilder($timelapse)
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
            ->getForm();

        $ftpForm = $this->createFormBuilder($ftpTransfert)
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
            ])
            ->getForm();

        $timelapseForm->handleRequest($request);
        $ftpForm->handleRequest($request);
        if ($timelapseForm->isSubmitted() && $timelapseForm->isValid()) {
            $timelapse = $timelapseForm->getData();
            $entityManager->persist($timelapse);
            $entityManager->flush();
            # write timelapse args in bash script
            return $this->redirectToRoute('timelapse_index');
        }elseif ($ftpForm->isSubmitted() && $ftpForm->isValid()) {
            $ftpTransfert = $ftpForm->getData();
            $entityManager->persist($ftpTransfert);
            $entityManager->flush();
            # if $ftpForm->getActive() is true 
            if ($ftpTransfert->getActive()) {
                # write the FTP parameters into bash script
            }
            return $this->redirectToRoute('timelapse_index');
        }

        if (null != $timelapse->getPath()) {
            # get the lasts pictures in the local path
            $pictures = glob($timelapse->getPath() . '/.' . $timelapse->getFileExtension());
        } else {
            # simulate pictures
            for ($i = 0; $i < rand(5, 50); $i++) {
                $pictures[] = 'https://via.placeholder.com/1920x1080';
            }
        }

        return $this->render('timelapse/timelapse.html.twig', [
            'timelapseForm' => $timelapseForm->createView(),
            'ftpForm' => $ftpForm->createView(),
            'pictures' => $pictures
        ]);
    }
}
