<?php

namespace Public\Modules\speedyvisuals\Classes\Subscriptions;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

final class Subscription
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("sv_user_subscriptions")) {
            $self->maintainTable();
            return;
        }

        $self->dbTable->query("CREATE TABLE sv_user_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            plan_slug VARCHAR(100) NOT NULL,
            status ENUM('active', 'cancelled', 'expired', 'paused') DEFAULT 'active',
            start_date DATE NOT NULL,
            end_date DATE,
            next_billing_date DATE,
            payment_method VARCHAR(100),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_id (user_id),
            INDEX idx_plan_slug (plan_slug)
        )")->execute();

        $self->dbTable->query("CREATE TABLE sv_subscription_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            previous_plan_slug VARCHAR(100),
            new_plan_slug VARCHAR(100),
            changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_id (user_id)
        )")->execute();
    }

    private function maintainTable()
    {
        // Add logic to modify the table if structure updates are needed in the future
    }

    public function getActiveSubscription($userId)
    {
        return $this->dbTable->select("sv_user_subscriptions")
            ->where("user_id", $userId, "i")
            ->and()->where("status", "active")
            ->execute()->row();
    }

    public function createSubscription($userId, $planSlug, $startDate, $endDate = null, $paymentMethod = null)
    {

        $this->cancelSubscription($userId);

        return $this->dbTable->insert("sv_user_subscriptions", "issss", [
            "user_id" => $userId,
            "plan_slug" => $planSlug,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "payment_method" => $paymentMethod
        ])->execute();
    }

    public function cancelSubscription($userId)
    {
        return $this->dbTable->update("sv_user_subscriptions")
            ->set("status", "cancelled")
            ->where("user_id", $userId, "i")
            ->and()->where("status", "active")
            ->execute();
    }

    public function recordSubscriptionChange($userId, $oldSlug, $newSlug)
    {
        return $this->dbTable->insert("sv_subscription_history", "iss", [
            "user_id" => $userId,
            "previous_plan_slug" => $oldSlug,
            "new_plan_slug" => $newSlug
        ])->execute();
    }

    public function switchPlan($userId, $newSlug)
    {
        $active = $this->getActiveSubscription($userId);

        if ($active) {
            $this->recordSubscriptionChange($userId, $active->plan_slug, $newSlug);

            return $this->dbTable->update("sv_user_subscriptions")
                ->set("plan_slug", $newSlug)
                ->set("start_date", date('Y-m-d'))
                ->set("status", "active")
                ->where("id", $active->id, "i")
                ->execute();
        }

        return $this->createSubscription($userId, $newSlug, date('Y-m-d'));
    }
}
