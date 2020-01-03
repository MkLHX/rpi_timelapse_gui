<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use App\Entity\Timelapse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TimelapseManageCron extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    protected function execute(InputInterface $input, OutputInterface $output, KernelInterface $kernel)
    {
        $output->writeln([
            '<info>==================================</info>',
            '<info>Set the timelapse crontab schedule</info>',
            '<info>==================================</info>',
            '',
        ]);

        $cron = $input->getOption('cron');
        if (!$cron) {
            $cronHelper = $this->getHelper('question');
            $cronQuestion = new Question('Please select the pictures resolution (by default every 15min */15 * * * *): ', '*/15 * * * *');
            $cron = $cronHelper->ask($input, $output, $cronQuestion);
            $output->writeln(["<info>You've scheduled timelapse execution every $cron</info>", '']);
        }

        /**
         * Get timelapse settings from db
         */
        $lastTimelapseConf = $this->em->getRepository(Timelapse::class)->findOneBy([], ['id' => 'DESC']);
        if (!$lastTimelapseConf) {
            // stop the command and show warning message about config missing
            $output->writeln([
                '<error>===============================================</error>',
                '<error>There is nothing in db about timelapse settings</error>',
                '<error>So i can\'t schedule timelapse</error>',
                '<error>===============================================</error>',
            ]);
            return 0;
        }

        /**
         * 1- get the current crontab content crontab -l
         * 2- get the project root dir
         * 3- create a tmp text file to store old and new cron job
         * 4- write crontab
         */
        //TODO check if any timelapse schedule exist
        exec("crontab -l", $outGetCron, $retGetCron);
        $cronjob = $lastTimelapseConf->getSchedule() . "php " . $kernel->getProjectDir()."bin/console app:timelapse:get-config-and-exec";
        file_put_contents('/tmp/crontab.txt', $retGetCron.$cronjob);
        exec("crontab /tmp/crontab.txt", $outCron, $retCron);
        $output->writeln(["<info>Crontab schedule done!</info>", '']);

        return 0;
    }
}