<?php

namespace Public\Modules\speedyvisuals\Classes\Project;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

class Proposal
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("svproposals")) {
            return;
        }

        $statement = "CREATE TABLE svproposals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id BIGINT(20) UNSIGNED NOT NULL,
            freelancer_id BIGINT(20) UNSIGNED NOT NULL,
            message TEXT,
            proposed_budget DECIMAL(10,2),
            proposed_deadline DATE,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES svproject(id),
            FOREIGN KEY (freelancer_id) REFERENCES users(id),
            INDEX idx_project_id (project_id),
            INDEX idx_freelancer_id (freelancer_id),
            INDEX idx_status (status)
        )";

        $self->dbTable->query($statement)->execute();
    }

    public function create($data)
    {
        $session = Session::getInstance();
        $user = $session->getResourceOwner();

        if (!$user) {
            http_response_code(401);
            return "Unauthorized";
        }

        return $this->dbTable->insert("svproposals", "iissd", [[
            "project_id" => $data['project_id'],
            "freelancer_id" => $user->user_id,
            "message" => $data['message'],
            "proposed_budget" => $data['proposed_budget'],
            "proposed_deadline" => $data['proposed_deadline']
        ]])->execute()->lastInsertId;
    }

    public function getByProject($projectId, $limit = 20, $offset = 0)
    {
        return $this->dbTable->select("svproposals")
            ->where("project_id", $projectId, "i")
            ->orderBy("submitted_at", "DESC")
            ->limit($limit)->offset($offset)
            ->execute()->rows();
    }

    public function getByFreelancer($freelancerId, $limit = 20, $offset = 0)
    {
        return $this->dbTable->select("svproposals")
            ->where("freelancer_id", $freelancerId, "i")
            ->orderBy("submitted_at", "DESC")
            ->limit($limit)->offset($offset)
            ->execute()->rows();
    }

    public function changeStatus($id, $status)
    {
        return $this->dbTable->update("svproposals")
            ->set("status", $status)
            ->where("id", $id, "i")
            ->execute();
    }

    public function delete($id)
    {
        return $this->dbTable->delete("svproposals")
            ->where("id", $id, "i")
            ->execute();
    }
}
