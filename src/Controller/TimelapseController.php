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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @Route("/", name="timelapse")
 */
class TimelapseController extends AbstractController
{

    /**
     * @Route("", name="_index")
     */
    public function index(Request $request, KernelInterface $kernel, EntityManagerInterface $entityManager, TimelapseRepository $timelapseRepository, FTPTransfertRepository $fTPTransfertRepository)
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
            // $timelapse->setPath($this->getParameter('app.timelapse_pics_dir'));
            $entityManager->persist($timelapse);
            $entityManager->flush();

            //TODO edit the crontab with cron command
            $app = new Application($kernel);
            $app->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:timelapse:cron',
                '-c' => $timelapse->getSchedule(),
            ]);

            $output = new BufferedOutput();
            $app->run($input, $output);
            $getCmdResult = $output->fetch();
            return $this->redirectToRoute('timelapse_index');
        }

        $ftpForm->handleRequest($request);
        if ($ftpForm->isSubmitted() && $ftpForm->isValid()) {
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
                //TODO show warning message in flash bag
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

    /**
     * @Route("/timelapse/snapshot", name="_snapshot")
     */
    public function test(Request $request,  KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:timelapse:get-config-and-exec',
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
        $getCmdResult = $output->fetch();
        return $this->redirectToRoute('timelapse_index');
    }
}
