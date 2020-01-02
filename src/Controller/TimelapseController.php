<?php

namespace App\Controller;

use App\Entity\FTPTransfert;
use App\Entity\Timelapse;
use App\Form\Type\FTPTransfertType;
use App\Form\Type\TimelapseType;
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
        $timelapseForm = $this->createForm(TimelapseType::class, $timelapse);
        $ftpForm = $this->createForm(FTPTransfertType::class, $ftpTransfert);

        $timelapseForm->handleRequest($request);
        if ($timelapseForm->isSubmitted() && $timelapseForm->isValid()) {
            $timelapse = $timelapseForm->getData();
            $entityManager->persist($timelapse);
            $entityManager->flush();
            return $this->redirectToRoute('timelapse_index');
        }

        $ftpForm->handleRequest($request);
        if ($ftpForm->isSubmitted() && $ftpForm->isValid()) {
            // $path = str_replace(' ','%20', $ftpForm['path']->getData());
            $ftpTransfert = $ftpForm->getData();
            $entityManager->persist($ftpTransfert);
            $entityManager->flush();
            return $this->redirectToRoute('timelapse_index');
        }

        if (null != $timelapse->getPath()) {
            # get the lasts pictures in the local path
            $globStr = $timelapse->getPath() . '/*.' . \strtolower($timelapse->getFileExtension());
            $pictures = glob($globStr);
            if (!$pictures) {
                //dump('glob errors, cannot find the right pictures path');
                // show warning message
            }
            if (count($pictures) == 0) {
                # simulate pictures
                for ($i = 0; $i < rand(5, 50); $i++) {
                    $pictures[] = 'https://via.placeholder.com/1920x1080';
                }
            }
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
