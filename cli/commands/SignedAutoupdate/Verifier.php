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
class Verifier extends Command
{
    /**
     * Configure the command line interface
     */
    protected function configure()
    {
        $this
            ->setName('verifier:verify')
            ->setDescription('Get someones infos')
            ->addArgument('signature', InputArgument::OPTIONAL, 'the path you want save the files', './')
            ->addArgument('key', InputArgument::OPTIONAL, 'the path you want save the files', './')
            ->addArgument('list', InputArgument::OPTIONAL, 'the path you want save the files', './');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input  the user input
     * @param OutputInterface $output the command line output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // is http fallback enabled
        $signature = $input->getArgument("signature");
        $key = $input->getArgument("key");
        $list = $input->getArgument("list");

        $signature = \ParagonIE_Sodium_Compat::hex2bin(file_get_contents($signature));
        $key = \ParagonIE_Sodium_Compat::hex2bin(file_get_contents($key));
        $list = file_get_contents($list);

        $result = \ParagonIE_Sodium_Compat::crypto_sign_verify_detached($signature, $list, $key);

        var_dump($result);
    }
}
