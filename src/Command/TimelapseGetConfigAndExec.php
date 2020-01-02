<?php

namespace App\Command;

use App\Entity\FTPTransfert;
use App\Entity\Timelapse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

class TimelapseGetConfigAndExec extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

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
        $io = new SymfonyStyle($input, $output);

        $command = $this->getApplication()->find('app:timelapse:exec');
        $output->writeln([
            '<info>===================================</info>',
            '<info>Get Timelapse configuration from db</info>',
            '<info>===================================</info>',
            '',
        ]);

        $lastTimelapseConf = $this->em->getRepository(Timelapse::class)->findOneBy([], ['id' => 'DESC']);
        $lastFTPConf = $this->em->getRepository(FTPTransfert::class)->findOneBy(['active' => 1], ['id' => 'DESC']);

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

        $arguments = [
            'command' => 'app:timelapse:exec',
            '-res' => $lastTimelapseConf->getResolution(),
            '-ext' => $lastTimelapseConf->getFileExtension(),
            '-pth' => $lastTimelapseConf->getPath(),
        ];

        if ($lastFTPConf && $lastFTPConf->getActive()) {
            // $arguments['-host'] = $lastFTPConf->getHost();
            // $arguments['-login'] = $lastFTPConf->getLogin();
            // $arguments['-pwd'] = $lastFTPConf->getPassword();
            // $arguments['-ftppth'] = $lastFTPConf->getPath();
            // TODO code the ftp part
            /**
             * Create a command who make ftp connection and pass on arg the local picture root path
             * Call the ftp command when picture is take
             * When picture is sent to the FTP server, remove the picture from the tmp folder
             * check if there is some arguments about ftp command before run it
             */
        }

        $execInput = new ArrayInput($arguments);
        $returnCode = $command->run($execInput, $output);

        return $returnCode;
    }
}
