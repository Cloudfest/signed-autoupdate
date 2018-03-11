<?php
namespace Command\SignedAutoupdate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * A Signer CLI class
 */
class Generator extends Command
{
    /**
     * Configure the command line interface
     */
    protected function configure()
    {
        $this
            ->setName('generator:generate')
            ->setDescription('Get someones infos')
            ->addArgument('path', InputArgument::OPTIONAL, 'the path you want save the files', './');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input  the user input
     * @param OutputInterface $output the command line output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $keyPair = \ParagonIE_Sodium_Compat::crypto_sign_keypair();
        $secretKey = \ParagonIE_Sodium_Compat::crypto_sign_secretkey($keyPair);
        $publicKey = \ParagonIE_Sodium_Compat::crypto_sign_publickey($keyPair);

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($path . 'secretkey.txt', \ParagonIE_Sodium_Compat::bin2hex($secretKey));
        $fileSystem->dumpFile($path . 'publickey.txt', \ParagonIE_Sodium_Compat::bin2hex($publicKey));
    }
}
