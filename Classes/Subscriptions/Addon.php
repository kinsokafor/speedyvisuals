<?php

namespace Public\Modules\speedyvisuals\Classes\Subscriptions;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

final class Addon
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("sv_user_addons")) {
            $self->maintainTable();
            return;
        }

        $self->dbTable->query("CREATE TABLE sv_user_addons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            addon_slug VARCHAR(100) NOT NULL,
            start_date DATE,
            end_date DATE,
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_id (user_id),
            INDEX idx_addon_slug (addon_slug)
        )")->execute();

        $self->dbTable->query("CREATE TABLE sv_addon_usage_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            addon_slug VARCHAR(100),
            used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            action VARCHAR(255),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_id (user_id),
            INDEX idx_addon_slug (addon_slug)
        )")->execute();
    }

    private function maintainTable()
    {
        // Extend for table alterations
    }

    public function assignAddon($userId, $addonSlug, $startDate = null, $endDate = null)
    {
        // Check if addon is already active for user
        $existing = $this->dbTable->select("sv_user_addons")
            ->where("user_id", $userId, "i")
            ->and()->where("addon_slug", $addonSlug)
            ->and()->where("end_date", null)
            ->execute()
            ->row();

        if ($existing) {
            return [
                "status" => "error",
                "message" => "You already have this add-on active."
            ];
        }

        $this->dbTable->insert("sv_user_addons", "isss", [[
            "user_id" => $userId,
            "addon_slug" => $addonSlug,
            "start_date" => $startDate ?: date('Y-m-d'),
            "end_date" => $endDate
        ]])->execute();

        return [
            "status" => "success",
            "message" => "Add-on assigned successfully."
        ];
    }

    public function logUsage($userId, $addonSlug, $action)
    {
        return $this->dbTable->insert("sv_addon_usage_logs", "iss", [[
            "user_id" => $userId,
            "addon_slug" => $addonSlug,
            "action" => $action
        ]])->execute();
    }

    public function getUserAddons($userId)
    {
        return $this->dbTable->select("sv_user_addons")
            ->where("user_id", $userId, "i")
            ->execute()
            ->result();
    }

    public function revokeAddon($userId, $addonSlug)
    {
        return $this->dbTable->update("sv_user_addons")
            ->set("end_date", date("Y-m-d"))
            ->where("user_id", $userId, "i")
            ->and()->where("addon_slug", $addonSlug)
            ->and()->where("end_date", null)
            ->execute();
    }

    public function hasAddon($userId, $addonSlug)
    {
        return $this->dbTable->select("sv_user_addons")
            ->where("user_id", $userId, "i")
            ->and()->where("addon_slug", $addonSlug)
            ->and()->where("end_date", null)
            ->execute()
            ->row();
    }
}