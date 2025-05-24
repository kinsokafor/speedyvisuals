<?php

namespace Public\Modules\speedyvisuals\Classes\Project;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

class Project
{
    public $dbTable;

    public $keys = ["id", "user_id", "description", "type", "meta", "status", "created_at", "updated_at"];

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("svproject")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE svproject (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            description TEXT,
            type VARCHAR(255) NOT NULL,
            meta JSON NOT NULL,
            status ENUM('pending', 'in progress', 'completed', 'declined') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX (user_id),
            INDEX (type),
            INDEX (status)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data) {
        extract($data);
        $session = Session::getInstance();

        if(!($user = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }

        $self = new self;

        $meta = [];

        foreach ($data as $key => $value) {
            if(!in_array($key, $self->keys)) {
                $meta[$key] = $value;
            }
        }

        $id = $self->dbTable->insert("svproject", "isssds", [
            "user_id" => (int) $user->user_id ?? 0,
            "description" => $description ?? "",
            "type" => $type ?? "not categorized",
            "meta" => json_encode($meta)
        ])->execute();

        return $self->dbTable->select("svproject")
            ->where("id", $id)
            ->execute()->row();
    }
}