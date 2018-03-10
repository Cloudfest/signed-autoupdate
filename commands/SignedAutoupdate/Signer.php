<?php
namespace Command\SignedAutoupdate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * A WebFinger CLI class
 *
 * @author Matthias Pfefferle <matthias@pfefferle.org>
 */
class Signer extends Command
{
    /**
     * Configure the command line interface
     */
    protected function configure()
    {
        $this
            ->setName('sign')
            ->setDescription('Get someones infos')
            ->setDefinition(array())
            ->addArgument('path', InputArgument::REQUIRED, 'the path you want to check')
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
        // is http fallback enabled
        $path = $input->getArgument("path");
        $algo = $input->getOption("algo");

        $finder = new Finder();
        $finder->files()->in($path);

        $signation = array();
        $signation['version'] = '1.0.0';
        $signation['algorithm'] = $algo;

        foreach ($finder as $file) {
            // dumps the relative path to the file
            $signation['signatures'][] = array(
                'file' => $file->getRelativePathname(),
                'hash' => hash_file($algo, $file->getRelativePathname())
            );
        }

        $fileSystem = new Filesystem();

        $fileSystem->mkdir($path . '/.well-known/');
        $fileSystem->dumpFile($path . '/.well-known/signatures.json', json_encode($signation, JSON_PRETTY_PRINT));
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
