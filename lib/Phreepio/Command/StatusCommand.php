<?php

namespace Phreepio\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends PhreepioCommand
{
    const PROGRESS_BAR_SIZE = 30;

    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Check the progress of translations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Fetching</info> translation statuses for <comment>phreepio.json</comment>");

        $translator = $this->getTranslator();

        foreach ($translator->getStatusIterator() as $source => $result)
        {
            if ($result->success) {

                $output->writeln('File: <info>'.$result->remotePath.'</info> locale: <info>'.$result->locale.'</info>');
                $output->writeln($this->getProgress($result->stringCount, $result->approvedStringCount, $result->completedStringCount));
            }
            else {
                $output->writeln('<error>Failed to fetch status "'.$result->remotePath.'" for locale "'.$result->locale.'"</error>');
                $output->writeln('<comment>'.$result->errorMessage.'</comment>');
            }
            $output->writeln('');
        }
    }

    private function getProgress($total, $approved, $translated)
    {
        $a = '<comment>=</comment>';
        $t = '<info>|</info>';

        $ratio = self::PROGRESS_BAR_SIZE / max($total, 1);

        $approvedTicks = floor($approved * $ratio);
        $translatedTicks = floor($translated * $ratio);

        $output = '[';
        for ($i=1; $i <= self::PROGRESS_BAR_SIZE; $i++) {
            if ($translatedTicks > $i)
                $output .= $t;
            elseif ($translatedTicks == $i)
                $output .= '>';
            elseif ($approvedTicks > $i)
                $output .= $a;
            elseif ($approvedTicks == $i)
                $output .= '>';
            else
                $output .= '-';
        }
        $output .= ']';

        $approvedPercent = floor($approved / max($total, 1) *100.0);
        $translatedPercent = floor($translated / max($total, 1) *100.0);

        return $output . ' '. $approvedPercent.'% A / '.$translatedPercent.'% T';
    }
}

