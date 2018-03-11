<?php

namespace LaravelNewsVkCommunity;

class Database
{
    /**
     * @var \PDO
     */
    private $connect;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->connect = new \PDO('sqlite:laravelnews.sqlite3');
        $this->connect->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param array $rows
     * @return array
     */
    public function fetchUnpublishedPosts(array $rows = []): array
    {

        return $this->connect
            ->query('SELECT ' . (empty($rows) ? '*' : implode(', ', $rows)) . ' FROM posts WHERE posted = 0')
            ->fetchAll();
    }

    /**
     * @param int $id
     * @return void
     */
    public function flagPostAsPublished(int $id): void
    {
        $statement = $this->connect->prepare('UPDATE posts SET posted = 1 WHERE id = :id');
        $statement->bindParam('id', $id);
        $statement->execute();
    }

    /**
     * @param string $url
     * @return bool
     */
    public function hasPost(string $url): bool
    {
        $statement = $this->connect->prepare('SELECT id FROM posts WHERE url = :url');
        $statement->bindParam('url', $url);
        $statement->execute();
        $row = $statement->fetch();

        return isset($row['id']) && 0 !== (int)$row['id'];
    }

    /**
     * @param string $title
     * @param string $url
     * @param string $tags
     * @return int
     */
    public function addPost(string $title = '', string $url = '', string $tags = '#general'): int
    {
        $statement = $this->connect->prepare('INSERT INTO posts(title, url, tags, posted) VALUES (:title, :url, :tags, 0)');
        $statement->execute(
            compact('title', 'url', 'tags')
        );

        return (int)$this->connect->lastInsertId();
    }

    /**
     * Migrate the database.
     *
     * @return void
     * @throws \Exception
     */
    public function migrate(): void
    {
        if ($this->hasMigrated()) {
            throw new \RuntimeException('Migration has been already executed!');
        }

        /* Create posts table */
        $this->connect->exec('
            CREATE TABLE `posts` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `title` TEXT NULL,
                `url` TEXT NOT NULL UNIQUE,
                `tags` TEXT NULL,
                `posted` INTEGER DEFAULT 0
            )
        ');
    }

    /**
     * @return bool
     */
    private function hasMigrated(): bool
    {
        $statement = $this->connect->prepare('
            SELECT name 
            FROM sqlite_master 
            WHERE type = :type 
            AND name = :name
        ');
        $statement->execute([
            'type' => 'table',
            'name' => 'posts',
        ]);

        $row = $statement->fetch();

        return isset($row['name']) && $row['name'] === 'posts';
    }
}