<?php

namespace LaravelNewsVkCommunity\Commands;

use LaravelNewsVkCommunity\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnpublishedPostsCommand extends Command
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @return void
     */
    public function configure(): void
    {
        $this
            ->setName('posts:unpublished')
            ->setDescription('Shows posts that were not published yet');

        $this->database = new Database;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $posts = $this->database->fetchUnpublishedPosts(['title', 'url', 'tags']);

        $table = new Table($output);
        $table->setHeaders(array_keys($posts[0]));
        $table->setRows($posts);
        $table->render();
    }
}