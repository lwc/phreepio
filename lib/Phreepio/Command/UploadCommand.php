<?php

namespace Phreepio\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCommand extends PhreepioCommand
{
    protected function configure()
    {
        $this
            ->setName('upload')
            ->setDescription('Push source files to the configured translator')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Pushing</info> sources defined in <comment>phreepio.json</comment>");
        $output->writeln('');

        $translator = $this->getTranslator();

        foreach ($translator->getUploadIterator() as $source => $resultPromise)
        {
            $resultPromise->then(function($result) use ($output, $source) {
                if ($result->success) {

                    $verb = "Added";
                    if ($result->overWritten) {
                        $verb = "<comment>Overwrote remote</comment>";
                    }
                    $output->writeln("$verb: <info>$source</info>");
                    $output->writeln("String Count: <info>{$result->stringCount}</info>");
                } else {
                    $output->writeln('<error>Failed to upload "' . $source . '"</error>');
                    $output->writeln('<comment>' . $result->errorMessage . '</comment>');
                }
                $output->writeln('');
            });
        }

        $translator->flush();
    }
}