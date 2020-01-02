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
            ->addOption('resolution', 'res', InputOption::VALUE_OPTIONAL, 'resolution of pictures')
            ->addOption('extension', 'ext', InputOption::VALUE_OPTIONAL, 'extension of pictures')
            ->addOption('path', 'path', InputOption::VALUE_OPTIONAL, 'local path where store pictures')
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln([
            '<info>==============</info>',
            '<info>Taking picture</info>',
            '<info>==============</info>',
            '',
        ]);

        $resolution = $input->getOption('resolution');
        if (!$resolution) {
            $resolHelper = $this->getHelper('question');
            $resolQuestion = new ChoiceQuestion(
                'Please select the pictures resolution (by default 384x288): ',
                ['384x288', '640x480', '1920x1080',],
                0
            );
            $resolQuestion->setErrorMessage('resolution %s is invalid.');
            $resolution = $resolHelper->ask($input, $output, $resolQuestion);
            $output->writeln("<info>You've selected $resolution has pictures resolution</info>");
        }

        $extension = $input->getOption('extension');
        if (!$extension) {
            $extHelper = $this->getHelper('question');
            $extQuestion = new ChoiceQuestion(
                'Please select the pictures extension (by default PNG): ',
                ['PNG', 'JPEG', 'JPG', 'MJPEG',],
                0
            );
            $extQuestion->setErrorMessage('extension %s is invalid.');
            $extension = $extHelper->ask($input, $output, $extQuestion);
            $output->writeln("<info>You've selected $extension has pictures extension</info>");
        }

        $localPath = $input->getOption('path');
        if (!$localPath) {
            $pathHelper = $this->getHelper('question');
            $pathQuestion = new Question('Please enter the local path location where pictures will store (by default timelapse): ', 'timelapse');
            $localPath = $pathHelper->ask($input, $output, $pathQuestion);
            $output->writeln("<info>You've selected public/$localPath has local path location</info>");
        }
        $date = new DateTime('now');
        $dateFormatted = $date->format('Y-m-d_H:i:s');
        $extension=\strtolower($extension);
        if(!file_exists($localPath) && !is_dir($localPath)){
            exec("mkdir public/$localPath", $outMakeDir, $retMakeDir);
            $output->writeln("<info>Local tmp folder public/$localPath has been created</info>");
        }
        // if(!is_writable($localPath)){
        //     exec("sudo chmod -R 755 $localPath", $outWritable, $retWritable);
        // }
        //TODO add condition if no folder creation error before sur fswebcam command
        exec("fswebcam -r $resolution --no-banner public/$localPath/$dateFormatted.$extension", $outTakePic, $retTakePic);
        $output->writeln("<info>Picture was taken</info>");

        // TODO code the ftp part
        /**
         * Create a command who make ftp connection and pass on arg the local picture root path
         * Call the ftp command when picture is take
         * When picture is sent to the FTP server, remove the picture from the tmp folder
         * check if there is some arguments about ftp command before run it
         */

        return 0;
    }
}
