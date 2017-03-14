<?php

namespace LaravelNewsVkCommunity\Commands;

use GuzzleHttp\Client;
use LaravelNewsVkCommunity\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PostCommand extends Command
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @var array
     */
    private $config;

    public function configure()
    {
        $this
            ->setName("posts:post")
            ->setDescription("Post unposted posts.");

        $this->database = new Database;
        $this->guzzle = new Client();
        $this->config = require_once __DIR__ . "/../../config.php";
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $posts = $this->database->getPosts();

        if (count($posts)) {
            foreach ($posts as $post) {
                $this->publish($post);
            }

            $output->writeln("Something was published.");
        } else {
            $output->writeln("Nothing to publish was found.");
        }
    }

    /**
     * @param array $post
     */
    private function publish(array $post)
    {
        $request = $this->guzzle->get("https://api.vk.com/method/wall.post", [
            'query' => [
                'owner_id' => $this->config['vk']['group_id'],
                'from_group' => 1,
                'message' => $post['title'] . "\n" . "\n" . $post['tags'] . " #LaravelNews #Laravel #PHP",
                'attachments' => $post['url'],
                'access_token' => $this->config['vk']['access_token']
            ]
        ]);

        $response = json_decode($request->getBody()->getContents(), true);

        if ($request->getStatusCode() == 200 && isset($response['response']['post_id'])) {
            $this->database->postWasPublished($post['id']);
        }
    }
}