<?php

namespace Clover\Dump\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Dump
 * @package Clover\Dump\Command
 */
class Dump extends Command
{
    /**
     * The path to the clover coverage XML file to parse.
     * @var string
     */
    protected $cloverFile;

    /**
     * Toggles whether to show coverage for every file, or just a summary of all files
     * @var boolean
     */
    protected $summaryOnly = false;

    /**
     * The percentage at which clover-dump should show a file's coverage as warning
     * @var int
     */
    protected $warningPercentage = 90;

    /**
     * The percentage at which clover-dump should show a file's coverage as error
     * @var int
     */
    protected $errorPercentage = 80;

    /**
     * The percentage at which clover-dump should return a failure value
     * @var int
     */
    protected $failAtPercentage = -1;

    /**
     * Set us up the command!
     */
    public function configure()
    {
        $this->setName('dump')
            ->setDescription('Dumps clover code coverage information to the screen');

        $this->addArgument(
            'clover-file',
            InputArgument::REQUIRED,
            'The path to the clover file'
        );

        $this->addOption(
            'summary',
            's',
            InputOption::VALUE_NONE,
            'Only show summary information'
        );

        $this->addOption(
            'warning-percentage',
            'w',
            InputOption::VALUE_OPTIONAL,
            'The percentage after which to start showing warning (yellow)',
            $this->warningPercentage
        );

        $this->addOption(
            'error-percentage',
            'e',
            InputOption::VALUE_OPTIONAL,
            'The percentage after which to start showing errors (red)',
            $this->errorPercentage
        );

        $this->addOption(
            'fail-at',
            'f',
            InputOption::VALUE_OPTIONAL,
            'The total coverage percentage after clover-dump returns a failure result',
            $this->failAtPercentage
        );
    }

    /**
     * Parses the clover XML file and spits out coverage results to the console.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cloverFile = $input->getArgument('clover-file');
        $this->summaryOnly = $input->getOption('summary');
        $this->warningPercentage = $input->getOption('warning-percentage');
        $this->errorPercentage = $input->getOption('error-percentage');

        if (!file_exists($this->cloverFile)) {
            $output->writeLn('<error>Clover file does not exist</error>');
            return 2;
        }

        try {
            $xml = simplexml_load_file($this->cloverFile);
        } catch (\Exception $e) {
            $output->writeLn('<error>Malformed XML detected</strong>');
            return 2;
        }

        $output->writeln('Clover Code Coverage Report:');
        $output->writeln('');

        $total = $covered = 0;

        foreach ($xml->xpath('//class') as $class) {
            $total += (int)$class->metrics['methods'];
            $covered += (int)$class->metrics['coveredmethods'];

            if (!$this->summaryOnly) {
                $coverage = number_format(
                    $class->metrics['methods'] == 0 ?
                        0 : ($class->metrics['coveredmethods'] / $class->metrics['methods']) * 100,
                    2
                );

                $color = $this->getColorForPercentage($coverage);

                $output->writeln(
                    ' - <fg=' . $color . '>' . str_pad($coverage, 6, ' ', STR_PAD_LEFT) .
                    '%</fg=' . $color . '> ' . $class['namespace'] . '\\' . $class['name']
                );
            }
        }

        $percentage = number_format($total == 0 ? 0 : ($covered / $total) * 100, 2);

        $color = $this->getColorForPercentage($percentage);
        $output->writeLn('<fg=' . $color . '>Code Coverage: ' . $percentage . '%</fg=' . $color . '>');

        if ($percentage <= $this->failAtPercentage) {
            return 1;
        }

        return 0;
    }

    /**
     * Determines which color to show the passed percentage as.
     *
     * @param float $percentage
     * @return string
     */
    protected function getColorForPercentage($percentage)
    {
        if ($percentage < $this->errorPercentage) {
            return 'red';
        } elseif ($percentage < $this->warningPercentage) {
            return 'yellow';
        } else {
            return 'green';
        }
    }
}
