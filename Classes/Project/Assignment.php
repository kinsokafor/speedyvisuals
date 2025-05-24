<?php

namespace Public\Modules\speedyvisuals\Classes\Project;

use EvoPhp\Resources\DbTable;

final class Assignment extends Project
{
    public $dbTable;

    public $keys = ["id", "user_id", "project_id", "amount", "currency", "meta", "status", "created_at", "updated_at"];

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("svassignment")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE svassignment (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            project_id BIGINT(20) UNSIGNED NOT NULL,
            `amount` DOUBLE,
            `currency` VARCHAR(30),
            meta JSON NOT NULL,
            status ENUM('pending', 'paid', 'refunded', 'declined', 'settled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (project_id) REFERENCES svproject(id),
            INDEX (user_id),
            INDEX (project_id),
            INDEX (status)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data) {
        extract($data);

        $self = new self;

        $meta = [];

        foreach ($data as $key => $value) {
            if(!in_array($key, $self->keys)) {
                $meta[$key] = $value;
            }
        }

        $extraData = [];
        $extraDataType = "";

        if(isset($amount)) {
            $extraData["amount"] = (double) $amount;
            $extraDataType .= "d";
        }

        if(isset($currency)) {
            $extraData["currency"] = $currency;
            $extraDataType .= "s";
        }

        $id = $self->dbTable->insert("svassignment", "iis$extraDataType", [
            "user_id" => (int) $user_id ?? 0,
            "project_id" => $project_id,
            "meta" => json_encode($meta),
            ...$extraData
        ])->execute();

        return $self->dbTable->select("svassignment")
            ->where("id", $id)
            ->execute()->row();
    }
}