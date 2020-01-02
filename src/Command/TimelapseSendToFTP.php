<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class TimelapseSendToFTP extends Command
{
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
        $io = new SymfonyStyle($input, $output);

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
            $output->writeln("<info>You've provide $host has ftp server</info>");
        }

        $login = $input->getOption('ftp_login');
        if (!$login) {
            $loginHelper = $this->getHelper('question');
            $loginQuestion = new Question(
                "Please provide the ftp login for $host: "
            );
            $login = $loginHelper->ask($input, $output, $loginQuestion);
            $output->writeln("<info>You've provide $login has ftp server login</info>");
        }

        $pwd = $input->getOption('ftp_pass');
        if (!$pwd) {
            $pwdHelper = $this->getHelper('question');
            $pwdQuestion = new Question(
                "Please provide the ftp password for $host: "
            );
            $pwd = $pwdHelper->ask($input, $output, $pwdQuestion);
            $output->writeln("<info>You've provide $pwd has ftp server password</info>");
        }

        $path = $input->getOption('ftp_path');
        if (!$path) {
            $pathHelper = $this->getHelper('question');
            $pathQuestion = new Question('Please enter the remote path location where pictures will store (by default ftp root "/"): ', '/');
            $path = $pathHelper->ask($input, $output, $pathQuestion);
            $output->writeln("<info>You've selected $path has remote pictures ftp location</info>");
        }

        $localPath = $input->getOption('local_path');
        if (!$localPath) {
            $pathHelper = $this->getHelper('question');
            $localPathQuestion = new Question('Please enter the local path location where pictures are stored (by default public/timelapse): ', 'public/timelapse');
            $localPath = $pathHelper->ask($input, $output, $localPathQuestion);
            $output->writeln("<info>You've selected $localPath has local pictures location</info>");
        }
        // TODO maybe unusefull because in future we don't ask for the local path to the user
        if (!file_exists($localPath) && !is_dir($localPath)) {
            $output->writeln("<error>Cannot find local pictures location</error>");
            return 0;
        }


        //get pictures from local path
        $globStr = "$localPath/*.{png,jpeg,jpg,mjpeg}";
        $pictures = glob($globStr, GLOB_BRACE);
        if (count($pictures) == 0) {
            // stop the command and show warning message if no pictures are found in localPath
            $output->writeln([
                '<error>===================================</error>',
                '<error>Cannot found pictures in $localPath</error>',
                '<error>Please provide good local path</error>',
                '<error>===================================</error>',
            ]);
            return 0;
        }
        dump($pictures);
        /**
         * # Call 1. Uses the ftp command with the -inv switches.
         * #-i turns off interactive prompting.
         * #-n Restrains FTP from attempting the auto-login feature.
         * #-v enables verbose and progress.
         *
         * ftp -inv ${FTP_HOST} << EOF
         *
         * # Call 2. Here the login credentials are supplied by calling the variables.
         *
         * user ${FTP_USER} ${PASS}
         *
         * # Call 3.  Here you will tell FTP to put or get the file.
         * put ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT} ${FTP_PICS_PATH}/$DATE.${PICS_EXT}"
         *
         * # End FTP Connection
         * bye
         * EOF

         * sleep 2
         */
        $output->writeln("<info>initialize connection to $host</info>");
        $cnx = ftp_connect($host) or die("Couldn't connect to $host");
        $output->writeln("<info>Connecting to $host with provided credentials</info>");
        if (ftp_login($cnx, $login, $pwd)) {
            $output->writeln("<info>Connected to $host</info>");
            $output->writeln("<info>Start sending pictures to $host</info>");
            foreach ($pictures as $currentPic) {
                ftp_put($cnx, $path/$currentPic, $localPath/$currentPic, FTP_ASCII);
            }
        }
        ftp_close($cnx);
        $output->writeln("<info>Pictures were sent</info>");

        //rm -f ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT}
        $output->writeln("<info>Pictures were removed</info>");

        return 0;
    }
}
