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

        $insertId = $self->dbTable->insert("svproject", "isss", [
            "user_id" => (int) $user->user_id ?? 0,
            "description" => $description ?? "",
            "type" => $type ?? "not categorized",
            "meta" => json_encode($meta)
        ])->execute();

        return $self->getById($insertId);
    }

    public function getById($id)
    {
        return $this->dbTable->select("svproject")
            ->where("id", $id, "i")
            ->limit(1)
            ->execute()->row();
    }

    public function getAllByUser($userId, $limit = 20, $offset = 0)
    {
        return $this->dbTable->select("svproject")
            ->where("user_id", $userId, "i")
            ->orderBy("created_at", "DESC")
            ->limit($limit)
            ->offset($offset)
            ->execute()->rows();
    }

    public function getCountByUser($userId, $status = "pending")
    {
        return $this->dbTable->select("svproject", "count(id) as c")
            ->where("user_id", $userId, "i")
            ->where("status", $status, "s")
            ->orderBy("created_at", "DESC")
            ->execute()->row()->c;
    }

    public function getAll($limit = 100, $offset = 0)
    {
        return $this->dbTable->select("svproject")
            ->orderBy("created_at", "DESC")
            ->limit($limit)
            ->offset($offset)
            ->execute()->rows();
    }

    public function search($filters = [], $limit = 50, $offset = 0)
    {
        $query = $this->dbTable->select("svproject");

        if (!empty($filters['user_id'])) {
            $query->where("user_id", $filters['user_id'], "i");
        }
        if (!empty($filters['status'])) {
            $query->and()->where("status", $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->and()->where("type", $filters['type']);
        }
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->and()->whereBetween("created_at", $filters['date_from'], $filters['date_to']);
        }
        if (!empty($filters['keyword'])) {
            $query->and()->whereLike("description", "%" . $filters['keyword'] . "%");
        }

        return $query->orderBy("created_at", "DESC")
            ->limit($limit)
            ->offset($offset)
            ->execute()->rows();
    }

    public function update($id, $data)
    {
        $project = $this->getById($id);
        if (!$project) return false;

        $updateFields = [];
        $meta = json_decode($project->meta ?? '{}', true);

        foreach ($data as $key => $value) {
            if (in_array($key, $this->keys)) {
                $updateFields[$key] = $value;
            } else {
                $meta[$key] = $value;
            }
        }

        $updateFields['meta'] = json_encode($meta);

        return $this->dbTable->update("svproject")
            ->setMultiple($updateFields)
            ->where("id", $id, "i")
            ->execute();
    }

    public function changeStatus($id, $status)
    {
        return $this->dbTable->update("svproject")
            ->set("status", $status)
            ->where("id", $id, "i")
            ->execute();
    }

    public function delete($id)
    {
        return $this->dbTable->delete("svproject")
            ->where("id", $id, "i")
            ->execute();
    }
}
