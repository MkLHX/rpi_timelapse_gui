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

        /** 
         * check if any timelapse cronjob exist
         * if yes, remove it
         */
        exec("crontab -l", $outGetCron, $retGetCron);
        die($outGetCron);
        if (null != $outGetCron) {
            // first find the comment line //TODO find bestter way
            $previousCronjobs = preg_grep("/# timelapse cronjob/", $outGetCron);
            // then delete the cronjob is the next line 
            foreach (array_keys($previousCronjobs) as $k) {
                unset($outGetCron[$k]);
                unset($outGetCron[$k + 1]);
            }
        }

        $cronjob = "$cron php " . $this->kernel->getProjectDir() . "/bin/console app:timelapse:get-config-and-exec";
        $tmpCrontabFilePath = $this->parameter->get('app.timelapse_pics_dir') . '/crontab.txt';

        // change permission
        exec("sudo chown www-data:www-data $tmpCrontabFilePath", $outChangePerm, $retchangePerm);

        $tmpCrontabFile = fopen($tmpCrontabFilePath, "w");
        // write existing cronjob
        foreach ($outGetCron as $key => $line) {
            fwrite($tmpCrontabFile, $line . PHP_EOL);
        }
        // add comment on cronjob line
        fwrite($tmpCrontabFile, "# timelapse cronjob" . PHP_EOL);
        // write cronjob
        fwrite($tmpCrontabFile, $cronjob . PHP_EOL);
        fclose($tmpCrontabFile);

        exec("crontab $tmpCrontabFilePath", $outCron, $retCron);
        $output->writeln(["<info>Crontab schedule done!</info>", '']);

        return 0;
    }
}
