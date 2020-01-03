<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TimelapseSendToFTP extends Command
{
    protected $parameter;

    public function __construct(ParameterBagInterface $parameter)
    {
        $this->parameter = $parameter;

        parent::__construct();
    }
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:timelapse:send-to-ftp';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Send timelapse pictures to ftp server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to send pictures from local folder to ftp server ...')
            // Arguments
            ->addOption('ftp_host', 'host', InputOption::VALUE_OPTIONAL, 'ftp host ip addess of domain')
            ->addOption('ftp_login', 'login', InputOption::VALUE_OPTIONAL, 'ftp login, blank if not needed')
            ->addOption('ftp_pass', 'pwd', InputOption::VALUE_OPTIONAL, 'ftp password, blank if not needed')
            ->addOption('ftp_path', 'ftppth', InputOption::VALUE_OPTIONAL, 'ftp path where store pics')
            ->addOption('local_path', 'locpth', InputOption::VALUE_OPTIONAL, 'local path where are stored pics you want send to ftp server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<info>==============================</info>',
            '<info>Sending pictures to FTP Server</info>',
            '<info>==============================</info>',
            '',
        ]);

        $host = $input->getOption('ftp_host');
        if (!$host) {
            $hostHelper = $this->getHelper('question');
            $hostQuestion = new Question(
                'Please provide the server hostname or ip address: ',
            );
            $host = $hostHelper->ask($input, $output, $hostQuestion);
            $output->writeln(["<info>You've provide $host has ftp server</info>", '']);
        }

        $login = $input->getOption('ftp_login');
        if (!$login) {
            $loginHelper = $this->getHelper('question');
            $loginQuestion = new Question(
                "Please provide the ftp login for $host: "
            );
            $login = $loginHelper->ask($input, $output, $loginQuestion);
            $output->writeln(["<info>You've provide $login has ftp server login</info>", '']);
        }

        $pwd = $input->getOption('ftp_pass');
        if (!$pwd) {
            $pwdHelper = $this->getHelper('question');
            $pwdQuestion = new Question(
                "Please provide the ftp password for $host: "
            );
            $pwd = $pwdHelper->ask($input, $output, $pwdQuestion);
            $output->writeln(["<info>You've provide $pwd has ftp server password</info>", '']);
        }

        $path = $input->getOption('ftp_path');
        if (!$path) {
            $pathHelper = $this->getHelper('question');
            $pathQuestion = new Question('Please enter the remote path location where pictures will store (by default ftp root "/"): ', '/');
            $path = $pathHelper->ask($input, $output, $pathQuestion);
            $output->writeln(["<info>You've selected $path has remote pictures ftp location</info>", '']);
        }

        $localPath = $input->getOption('local_path');
        if (!$localPath) {
            $pathHelper = $this->getHelper('question');
            $localPathQuestion = new Question('Please enter the local path location where pictures are stored (by default ~/public/timelapse_pics): ', $this->parameter->get('app.timelapse_pics_dir'));
            $localPath = $pathHelper->ask($input, $output, $localPathQuestion);
            $output->writeln(["<info>You've selected $localPath has local pictures location</info>", '']);
        }
        
        // TODO maybe unusefull because in future we don't ask for the local path to the user
        if (!file_exists($localPath) && !is_dir($localPath)) {
            $output->writeln(["<error>Cannot find local pictures location $localPath</error>", '']);
            return 0;
        }

        //get pictures from local path
        $globStr = "$localPath/*.{png,jpeg,jpg,mjpeg}";
        $pictures = glob($globStr, GLOB_BRACE);
        if (count($pictures) == 0) {
            // stop the command and show warning message if no pictures are found in localPath
            $output->writeln([
                '<error>===================================</error>',
                "<error>Cannot found pictures in $localPath</error>",
                '<error>Please provide good local path</error>',
                '<error>===================================</error>',
            ]);
            return 0;
        }
        $output->writeln(["<info>initialize connection to $host</info>", '']);
        $cnx = ftp_connect($host) or die("Couldn't connect to $host");
        $output->writeln(["<info>Connecting to $host with provided credentials</info>", '']);
        if (ftp_login($cnx, $login, $pwd)) {
            $output->writeln(["<info>Connected to $host</info>", '']);
            $output->writeln(["<info>Start sending pictures to $host</info>", '']);
            $progressBar = new ProgressBar($output, 100);
            $progressBar->setFormat('debug');
            foreach ($progressBar->iterate($pictures) as $currentPic) {
                $picName = preg_split("/\//", $currentPic);
                $fullPathPic = "$path/$picName[6]";
                ftp_put($cnx, $fullPathPic, $currentPic, FTP_ASCII);
            }
        }
        ftp_close($cnx);
        $output->writeln(['', "<info>Pictures were sent</info>", '']);

        
        //TODO give the choice to auto remove pics from local path when they're sent to ftp server
        //rm -f ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT}
        // $output->writeln(["<info>Pictures were removed</info>", '']);

        return 0;
    }
}
