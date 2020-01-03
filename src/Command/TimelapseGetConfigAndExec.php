<?php

namespace App\Command;

use App\Entity\FTPTransfert;
use App\Entity\Timelapse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TimelapseGetConfigAndExec extends Command
{
    private $em;
    protected $parameter;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameter)
    {
        $this->em = $em;
        $this->parameter = $parameter;
        parent::__construct();
    }
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:timelapse:get-config-and-exec';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Get the timelapse configuration from sqlite')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to get the user\'s timelapse configuration from sqlite');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>===================================</info>',
            '<info>Getting timelapse configuration from db</info>',
            '<info>===================================</info>',
            '',
        ]);

        /**
         * Get timelapse settings from db
         */
        $lastTimelapseConf = $this->em->getRepository(Timelapse::class)->findOneBy([], ['id' => 'DESC']);
        if (!$lastTimelapseConf) {
            // stop the command and show warning message about config missing
            $output->writeln([
                '<error>===============================================</error>',
                '<error>There is nothing in db about timelapse settings</error>',
                '<error>So i can\'t take pics</error>',
                '<error>===============================================</error>',
            ]);
            return 0;
        }
        $execArguments = [
            'command' => 'app:timelapse:exec',
            '-res' => $lastTimelapseConf->getResolution(),
            '-ext' => $lastTimelapseConf->getFileExtension(),
            '-pth' => $lastTimelapseConf->getPath(),
        ];
        $output->writeln([
            '<info>Timelapse Configuration loaded</info>',
            '',
        ]);
        $execInput = new ArrayInput($execArguments);
        $execCommand = $this->getApplication()->find('app:timelapse:exec');
        $execReturnCode = $execCommand->run($execInput, $output);

        /**
         * I ftp settings are found in db
         * get them and send pics to ftp server
         */
        $lastFTPConf = $this->em->getRepository(FTPTransfert::class)->findOneBy(['active' => 1], ['id' => 'DESC']);
        if (!$lastFTPConf) {
            // stop the command and show warning message about config missing
            $output->writeln([
                '<error>===============================================</error>',
                '<error>There is nothing in db about ftp settings</error>',
                '<error>So i can\'t send pics to ftp sever</error>',
                '<error>===============================================</error>',
            ]);
            return 0;
        }
        if ($lastFTPConf->getActive()) {
            $ftpArguments = [
                'command' => 'app:timelapse:send-to-ftp',
                '-host' => $lastFTPConf->getHost(),
                '-login' => $lastFTPConf->getLogin(),
                '-pwd' => $lastFTPConf->getPassword(),
                '-ftppth' => $lastFTPConf->getPath(),
                // '-locpth' => $lastTimelapseConf->getPath(),
                '-locpth' => $this->parameter->get('app.timelapse_pics_dir'),
            ];
            $output->writeln([
                '<info>FTP Configuration loaded</info>',
                '',
            ]);
            $ftpInput = new ArrayInput($ftpArguments);
            $ftpCommand = $this->getApplication()->find('app:timelapse:send-to-ftp');
            $ftpReturnCode = $ftpCommand->run($ftpInput, $output);
        }

        return $execReturnCode + $ftpReturnCode;
    }
}
