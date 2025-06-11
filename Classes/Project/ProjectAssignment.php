<?php

namespace Public\Modules\speedyvisuals\Classes\Project;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

class ProjectAssignment
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("project_assignments")) {
            return;
        }

        $statement = "CREATE TABLE project_assignments (
            project_id BIGINT(20) UNSIGNED PRIMARY KEY,
            freelancer_id BIGINT(20) UNSIGNED NOT NULL,
            start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME,
            FOREIGN KEY (project_id) REFERENCES svproject(id),
            FOREIGN KEY (freelancer_id) REFERENCES users(id),
            INDEX idx_freelancer_id (freelancer_id)
        )";

        $self->dbTable->query($statement)->execute();
    }

    public function assign($projectId, $freelancerId)
    {
        // Prevent duplicate assignment
        $existing = $this->dbTable->select("project_assignments")
            ->where("project_id", $projectId, "i")
            ->limit(1)->execute()->row();

        if ($existing) {
            return ["status" => "error", "message" => "Project already assigned."];
        }

        $this->dbTable->insert("project_assignments", "ii", [[
            "project_id" => $projectId,
            "freelancer_id" => $freelancerId
        ]])->execute();

        return ["status" => "success", "message" => "Project assigned."];
    }

    public function markCompleted($projectId)
    {
        return $this->dbTable->update("project_assignments")
            ->set("completed_at", date("Y-m-d H:i:s"))
            ->where("project_id", $projectId, "i")
            ->execute();
    }

    public function getByFreelancer($freelancerId, $limit = 20, $offset = 0)
    {
        return $this->dbTable->select("project_assignments")
            ->where("freelancer_id", $freelancerId, "i")
            ->orderBy("start_date", "DESC")
            ->limit($limit)
            ->offset($offset)
            ->execute()->rows();
    }

    public function getByProject($projectId)
    {
        return $this->dbTable->select("project_assignments")
            ->where("project_id", $projectId, "i")
            ->limit(1)
            ->offset(0)
            ->execute()->row();
    }

    public function unassign($projectId)
    {
        return $this->dbTable->delete("project_assignments")
            ->where("project_id", $projectId, "i")
            ->execute();
    }
}
