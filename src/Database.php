<?php

namespace LaravelNewsVkCommunity;

class Database
{
    /**
     * @var \PDO
     */
    private $connect;

    public function __construct()
    {
        $this->connect = new \PDO("sqlite:laravelnews.sqlite3");
        $this->connect->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );
    }

    /**
     * @return array|bool
     */
    public function getPosts()
    {
        $statement = $this->connect->prepare("SELECT * FROM posts WHERE posted = 0");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function postWasPublished(int $id)
    {
        $statement = $this->connect->prepare("UPDATE posts SET posted = 1 WHERE id = " . $id);
        $statement->execute();

        return true;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function hasPost(string $url = "") : bool
    {
        $statement = $this->connect->prepare("SELECT id FROM posts WHERE url = :url");
        $statement->execute(compact('url'));
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return isset($row['id']) && intval($row['id']) > 0;
    }

    /**
     * @param string $title
     * @param string $url
     * @param string $tags
     *
     * @return int
     */
    public function addPost(string $title = "", string $url = "", string $tags = "#general") : int
    {
        $statement = $this->connect->prepare("INSERT INTO posts(title, url, tags, posted) VALUES (:title, :url, :tags, 0)");
        $statement->execute(compact('title', 'url', 'tags'));

        return $this->connect->lastInsertId() || 0;
    }

    /**
     * Migrate database.
     *
     * @return bool
     * @throws \Exception
     */
    public function migrate() : bool
    {
        if ($this->hasMigrated()) {
            throw new \Exception("Migration already has been executed!");
        }

        /* Create posts table */
        $this->connect->query("
            CREATE TABLE `posts` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `title` TEXT NULL,
                `url` TEXT NOT NULL UNIQUE,
                `tags` TEXT NULL,
                `posted` INTEGER DEFAULT 0
            )
        ");

        return true;
    }

    /**
     * Has already migrated.
     *
     * @return bool
     */
    private function hasMigrated()
    {
        $statement = $this->connect->prepare("
            SELECT name 
            FROM sqlite_master 
            WHERE type = :type 
            AND name = :name
        ");
        $statement->execute([
            'type' => "table",
            'name' => "posts",
        ]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return isset($row['name']) && $row['name'] == "posts";
    }
}