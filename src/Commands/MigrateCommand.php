<?php

namespace LaravelNewsVkCommunity\Commands;

use LaravelNewsVkCommunity\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    /**
     * @return void
     */
    public function configure(): void
    {
        $this
            ->setName('migration:proceed')
            ->setDescription('Proceed migration scripts.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        (new Database)->migrate();

        $output->writeln('Migrated');
    }
}