<?php

namespace LaravelNewsVkCommunity\Commands;

use LaravelNewsVkCommunity\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    /**
     * @var Database
     */
    private $database;

    /**
     * Configure.
     *
     * @return void
     */
    public function configure()
    {
        $this
            ->setName("migration:proceed")
            ->setDescription("Proceed migration scripts.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->database = new Database;

        if ($this->database->migrate()) {
            $output->writeln("Database migration has successfully ended.");
        }
    }
}