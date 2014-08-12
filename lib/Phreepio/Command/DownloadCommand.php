<?php

namespace Phreepio\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends PhreepioCommand
{
    protected function configure()
    {
        $this
            ->setName('download')
            ->setDescription('Fetch translations from the configured translator')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Fetching</info> translations defined in <comment>phreepio.json</comment>");

        $translator = $this->getTranslator();

        foreach ($translator->getDownloadIterator() as $i => $result)
        {
            if ($result->success) {
                if ($result->usedCache) {
                    $output->writeln('Found cache: <comment>'.$result->remotePath.'</comment> for locale <comment>'.$result->locale.'</comment>');
                } else {
                    $output->writeln('<info>Succeeded</info> to download <comment>'.$result->remotePath.'</comment> for locale <comment>'.$result->locale.'</comment>');
                }

                $output->writeln('Translation saved in <comment>'.$result->localPath.'</comment>');
            }
            else {
                $output->writeln('<error>Failed to download "'.$result->remotePath.'" for locale "'.$result->locale.'"</error>');
                $output->writeln('<comment>'.$result->errorMessage.'</comment>');
            }
            $output->writeln('');
        }
    }
}
