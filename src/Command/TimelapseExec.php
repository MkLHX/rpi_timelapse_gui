<?php

namespace App\Command;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class TimelapseExec extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:timelapse:exec';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Execute the timelapse bash command')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to make snapshot from usb camera ...')
            // Arguments
            //fswebcam -r ${PICS_RESOLUTION} -p ${PICS_EXT} --no-banner ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT}
            ->addOption('resolution', 'res', InputOption::VALUE_OPTIONAL, 'resolution of pics')
            ->addOption('extension', 'ext', InputOption::VALUE_OPTIONAL, 'extension of pics')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'local path where store pics')
            ->addOption('ftp_host', 'host', InputOption::VALUE_OPTIONAL, 'ftp host ip addess of domain')
            ->addOption('ftp_login', 'login', InputOption::VALUE_OPTIONAL, 'ftp login, blank if not needed')
            ->addOption('ftp_pass', 'pwd', InputOption::VALUE_OPTIONAL, 'ftp password, blank if not needed')
            ->addOption('ftp_path', 'ftppath', InputOption::VALUE_OPTIONAL, 'ftp path where store pics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln([
            'Execute Timelapse',
            '============',
            '',
        ]);

        $resolution = $input->getOption('resolution');
        if (!$resolution) {
            $resolHelper = $this->getHelper('question');
            $resolQuestion = new ChoiceQuestion(
                'Please select the pics resolution (by default 384x288): ',
                ['384x288', '640x480', '1920x1080',],
                0
            );
            $resolQuestion->setErrorMessage('resolution %s is invalid.');
            $resolution = $resolHelper->ask($input, $output, $resolQuestion);
            $output->writeln("You've selected $resolution has pics resolution");
        }

        $extension = $input->getOption('extension');
        if (!$extension) {
            $extHelper = $this->getHelper('question');
            $extQuestion = new ChoiceQuestion(
                'Please select the pics extension (by default PNG): ',
                ['PNG', 'JPEG', 'JPG', 'MJPEG',],
                0
            );
            $extQuestion->setErrorMessage('extension %s is invalid.');
            $extension = $extHelper->ask($input, $output, $extQuestion);
            $output->writeln("You've selected $extension has pics extension");
        }

        $localPath = $input->getOption('path');
        if (!$localPath) {
            $pathHelper = $this->getHelper('question');
            $pathQuestion = new Question('Please enter the path location for pics storage (by default /tmp/timelapse): ', '/tmp/timelapse');
            $localPath = $pathHelper->ask($input, $output, $pathQuestion);
            $output->writeln("You've selected $localPath has pics local path");
        }
        $date = new DateTime('now');
        $dateFormatted = $date->format('Y-m-d_H:i:s');
        $extension=\strtolower($extension);
        if(!file_exists($localPath) && !is_dir($localPath)){
            \mkdir($localPath);
        }
        if(!is_writable($localPath)){
            exec("sudo chmod -R 755 $localPath", $outWritable, $retWritable);
        }
        exec("fswebcam -r $resolution --no-banner $localPath/$dateFormatted.$extension", $outTakePic, $retTakePic);

        // TODO code the ftp part

        return 0;
    }
}
