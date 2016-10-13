<?php

namespace LaravelNewsVkCommunity\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRssCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName("rss:check")
            ->setDescription("Check LaravelNews's RSS feed.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Successfully");
    }
}