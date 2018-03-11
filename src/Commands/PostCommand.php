<?php

namespace LaravelNewsVkCommunity\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use LaravelNewsVkCommunity\Database;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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
     * @var int
     */
    private $publishedCount = 0;

    /**
     * @var array
     */
    private $failedResponses = [];

    /**
     * @return void
     */
    public function configure(): void
    {
        $this
            ->setName('posts:post')
            ->setDescription('Post unposted posts.');

        $this->database = new Database;
        $this->guzzle = new Client;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $posts = $this->database->fetchUnpublishedPosts();

        if (!empty($posts)) {
            foreach ($posts as $post) {
                $this->publish($post);
            }

            $output->writeln(
                sprintf('%d out of %d posts were published', $this->publishedCount, \count($posts))
            );

            if (!empty($this->failedResponses)) {
                $output->writeln('Take a look at failed requests');

                $table = new Table($output);
                $table->setHeaders(['code', 'message']);

                foreach ($this->failedResponses as $response) {
                    $table->addRow([
                        'code' => $response['error']['error_code'],
                        'message' => $response['error']['error_msg'],
                    ]);
                }

                $table->render();
            }
        } else {
            $output->writeln('There are no posts to publish');
        }
    }

    /**
     * @param array $post
     * @return void
     */
    private function publish(array $post): void
    {
        $request = $this->sendRequest($post);
        $response = json_decode($request->getBody()->getContents(), true);

        if (isset($response['response']['post_id']) && $request->getStatusCode() === 200) {
            $this->database->flagPostAsPublished($post['id']);
            $this->publishedCount++;
        } else {
            $this->failedResponses[] = $response;
        }
    }

    /**
     * @param array $post
     * @return ResponseInterface
     */
    private function sendRequest(array $post): ResponseInterface
    {
        $uri = new Uri('https://api.vk.com/method/wall.post');
        $options = [
            'query' => [
                'v' => getenv('VK_API_VERSION'),
                'owner_id' => getenv('VK_GROUP_ID'),
                'from_group' => 1,
                'message' => $post['title'] . "\n" . "\n" . $post['tags'] . ' #LaravelNews #Laravel #PHP',
                'attachments' => $post['url'],
                'access_token' => getenv('VK_ACCESS_TOKEN'),
            ],
        ];

        return $this->guzzle->get($uri, $options);
    }
}