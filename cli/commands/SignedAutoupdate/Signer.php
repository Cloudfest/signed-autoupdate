<?php
namespace Command\SignedAutoupdate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * A Signer CLI class
 */
class Signer extends Command
{
    /**
     * Configure the command line interface
     */
    protected function configure()
    {
        $this
            ->setName('signer:sign')
            ->setDescription('Get someones infos')
            ->setDefinition(array())
            ->addArgument('path', InputArgument::REQUIRED, 'the path you want to check')
            ->addArgument('key', InputArgument::REQUIRED, 'the path to the secret key')
            ->addOption('algo', 'a', InputOption::VALUE_OPTIONAL, 'The algorithm', 'sha1');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input  the user input
     * @param OutputInterface $output the command line output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();

        // is http fallback enabled
        $path = $input->getArgument("path");
        $algo = $input->getOption("algo");

        // build path if not existing
        $fileSystem->mkdir($path . '/.well-known/');

        $jsonData = $this->executeLister($path, $algo);

        $key = $input->getArgument("key");

        $this->executeSigner($key, $path, $jsonData);
    }

    /**
     *
     */
    protected function executeLister($path, $algo) {
        $finder = new Finder();
        $finder->files()->in($path);

        $signation = array();
        $signation['version'] = '1.0.0';
        $signation['algorithm'] = $algo;

        foreach ($finder as $file) {
            // dumps the relative path to the file
            $signation['signatures'][] = array(
                'file' => $file->getRelativePathname(),
                'hash' => \hash_file($algo, $file->getRelativePathname())
            );
        }

        $fileSystem = new Filesystem();

        // generate JSON data
        $jsonData = json_encode($signation, JSON_PRETTY_PRINT);

        $fileSystem->dumpFile($path . '/.well-known/list.json', $jsonData);

        return $jsonData;
    }

    /**
     * Generate signature.txt file
     *
     * @param string $key the path to a key file
     * @param string $jsonData the json list
     */
    public function executeSigner($key, $path, $jsonData) {
        $secretKey = file_get_contents($key);
        $secretKey = \ParagonIE_Sodium_Compat::hex2bin($secretKey);

        $signature = \ParagonIE_Sodium_Compat::crypto_sign_detached($jsonData, $secretKey);
        $signature = \ParagonIE_Sodium_Compat::bin2hex($signature);

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($path . '/.well-known/signature.txt', $signature);
    }

    /**
     * handle errors
     *
     * @param \Exception                                       $error  the error stack
     * @param Symfony\Component\Console\Output\OutputInterface $output the ooutput interface
     * @param string                                           $prefix the prefix to indent the text
     */
    protected function displayError(\Exception $error, OutputInterface $output, $prefix = '')
    {
        $output->writeln('<error>' . $prefix . $error->getMessage() . '</error>');
        if ($error->getPrevious()) {
            $this->displayError($error->getPrevious(), $output, "\t - ");
        }
    }
}
