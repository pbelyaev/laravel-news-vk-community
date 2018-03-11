<?php

namespace LaravelNewsVkCommunity\Commands;

use SimplePie;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LaravelNewsVkCommunity\Database;

class CheckRssCommand extends Command
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var SimplePie
     */
    private $feed;

    /**
     * @var int
     */
    private $added = 0;

    /**
     * @return void
     */
    public function configure(): void
    {
        $this
            ->setName('rss:check')
            ->setDescription("Check LaravelNews's RSS feed.");

        $this->database = new Database;
        $this->feed = new SimplePie();
        $this->feed->set_feed_url('http://feed.laravel-news.com/');
        $this->feed->enable_cache(false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->feed->init();

        if (0 !== $this->feed->get_item_quantity()) {
            foreach ($this->feed->get_items() as $item) {
                $this->addPost(
                    $this->generatePostFromItem($item)
                );
            }

            $this->displayOutput($output);
        } else {
            throw new \RuntimeException('There are no items in RSS feed');
        }
    }

    /**
     * @param \SimplePie_Item $item
     * @return array
     */
    private function generatePostFromItem(\SimplePie_Item $item): array
    {
        $title = $item->get_title();
        $url = $item->get_links()[0];
        $tags = [];

        if (!empty($item->get_categories())) {
            $tags = $this->extractHashTagsFromCategories($item);
        }

        return compact('url', 'title', 'tags');
    }

    /**
     * @param \SimplePie_Item $item
     * @return array
     */
    private function extractHashTagsFromCategories(\SimplePie_Item $item): array
    {
        $tags = [];

        foreach($item->get_categories() as $category) {
            $tags[] = '#' . preg_replace("/[\s\.]*/", '', $category->get_term());
        }

        return $tags;
    }

    /**
     * @param array $post
     * @return void
     */
    private function addPost(array $post): void
    {
        if (!$this->database->hasPost($post['url'])) {
            $this->database->addPost($post['title'], $post['url'], implode(' ', $post['tags']));
            $this->added++;
        }
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function displayOutput(OutputInterface $output): void
    {
        $message = 'There is nothing new since the last check';

        if (0 !== $this->added) {
            $addedMoreThanOne = $this->added > 1;
            $message = sprintf(
                '%d %s %s added to the database',
                $this->added, ($addedMoreThanOne ? 'items' : 'item'),
                ($addedMoreThanOne ? 'were' : 'was')
            );
        }

        $output->writeln($message);
    }
}
