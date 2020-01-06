<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use App\Entity\Timelapse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TimelapseManageCron extends Command
{
    private $em;
    protected $parameter;
    protected $kernel;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameter, KernelInterface $kernel)
    {
        $this->em = $em;
        $this->parameter = $parameter;
        $this->kernel = $kernel;
        parent::__construct();
    }

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:timelapse:cron';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Manage crontab schedule')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you schedule the timelapse execution...')
            // Arguments
            ->addOption('cron', '-c', InputOption::VALUE_OPTIONAL, 'crontab config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>==================================</info>',
            '<info>Set the timelapse crontab schedule</info>',
            '<info>==================================</info>',
            '',
        ]);

        //TODO regex validation for cronjob format
        $cron = $input->getOption('cron');
        if (!$cron) {
            $cronHelper = $this->getHelper('question');
            $cronQuestion = new Question('Please provide the cronjob schedule (by default every 15min */15 * * * *): ', '*/15 * * * *');
            $cron = $cronHelper->ask($input, $output, $cronQuestion);
            $output->writeln(["<info>You've scheduled timelapse execution every $cron</info>", '']);
        }

        /**
         * 1- get the current crontab content crontab -l
         * 2- get the project root dir
         * 3- create a tmp text file to store old and new cron job
         * 4- write crontab
         */
        //TODO check if any timelapse schedule exist
        exec("crontab -l", $outGetCron, $retGetCron);
        dump($retGetCron);
        // $cronjob = $cron . "php " . $this->parameter->get('%kernel.project_dir%')."bin/console app:timelapse:get-config-and-exec";
        $cronjob = $cron . "php " . $this->kernel->getProjectDir() . "bin/console app:timelapse:get-config-and-exec";
        $tmpCrontabFilePath = $this->parameter->get('app.timelapse_pics_dir') . '/crontab.txt';
        // file_put_contents($tmpCrontabFile, $retGetCron . $cronjob . PHP_EOL);
        $tmpCrontabFile = fopen($tmpCrontabFilePath, "w+");
        fwrite($tmpCrontabFile, $retGetCron . $cronjob . PHP_EOL);
        exec("crontab $tmpCrontabFilePath", $outCron, $retCron);
        $output->writeln(["<info>Crontab schedule done!</info>", $retCron, '']);

        return 0;
    }
}
