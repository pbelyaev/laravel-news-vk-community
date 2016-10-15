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
     * Configure.
     *
     * @return void
     */
    public function configure()
    {
        $this
            ->setName("rss:check")
            ->setDescription("Check LaravelNews's RSS feed.");

        $this->database = new Database;
        $this->feed = new SimplePie();
        $this->feed->set_feed_url("http://feed.laravel-news.com/");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->feed->init();

        if (!$this->feed->get_items()) {
            throw new \Exception("Something went wrong...");
        }

        $toPost = [];

        foreach ($this->feed->get_items() as $item) {
            $title = $item->get_title();
            $url = $item->get_links()[0];
            $tags = [];

            if (count($item->get_categories())) {
                foreach($item->get_categories() as $category) {
                    $tags[] = sprintf("#%s", preg_replace("/\s*/", "", $category->get_term()));
                }
            }

            $toPost[] = compact('title', 'url', 'tags');
        }

        if (count($toPost) > 0) {
            $toPost = array_reverse($toPost);

            foreach($toPost as $post) {
                if (!$this->database->hasPost($post['url'])) {
                    $this->database->addPost($post['title'], $post['url'], implode(" ", $post['tags']));
                    $this->added++;
                }
            }
        }

        if ($this->added > 0) {
            $output->writeln(sprintf("There was/were %d new item(s)", $this->added));
        } else {
            $output->writeln("There is nothing new since the last check");
        }
    }
}