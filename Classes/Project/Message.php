<?php

namespace Public\Modules\speedyvisuals\Classes\Project;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;
use EvoPhp\Actions\Notifications\Notifications;

class Message
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("svmessages")) return;

        $statement = "CREATE TABLE svmessages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id BIGINT(20) UNSIGNED NOT NULL,
            receiver_id BIGINT(20) UNSIGNED NOT NULL,
            project_id BIGINT(20) UNSIGNED NOT NULL,
            content TEXT,
            sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            FOREIGN KEY (receiver_id) REFERENCES users(id),
            FOREIGN KEY (project_id) REFERENCES svproject(id),
            INDEX idx_sender_id (sender_id),
            INDEX idx_receiver_id (receiver_id),
            INDEX idx_project_id (project_id)
        )";

        $self->dbTable->query($statement)->execute();
    }

    public function send($data)
    {
        $session = Session::getInstance();
        $sender = $session->getResourceOwner();

        if (!$sender) {
            http_response_code(401);
            return "Unauthorized";
        }

        $messageId = $this->dbTable->insert("svmessages", "iiis", [[
            "sender_id" => $sender->user_id,
            "receiver_id" => $data['receiver_id'],
            "project_id" => $data['project_id'],
            "content" => $data['content']
        ]])->execute()->lastInsertId;

        $this->notifyReceiver($data['receiver_id'], $sender->user_id, $data['content']);

        return $messageId;
    }

    protected function notifyReceiver($receiverId, $senderId, $content)
    {
        $notif = new Notifications($content, "New Message", [
            "sender_id" => $senderId
        ]);

        $notif->to($receiverId)
            //   ->action("/dashboard/messages")
              ->log()
              ->mail()
              ->text();
    }

    public function getConversation($userId1, $userId2, $projectId, $limit = 50, $offset = 0)
    {
        return $this->dbTable->select("svmessages")
            ->openGroup()
                ->where("sender_id", $userId1, "i")
                ->and()->where("receiver_id", $userId2, "i")
            ->closeGroup()
            ->or()
            ->openGroup()
                ->where("sender_id", $userId2, "i")
                ->and()->where("receiver_id", $userId1, "i")
            ->closeGroup()
            ->and()->where("project_id", $projectId, "i")
            ->orderBy("sent_at", "ASC")
            ->limit($limit)->offset($offset)
            ->execute()->result();
    }

    public function delete($id)
    {
        return $this->dbTable->delete("svmessages")
            ->where("id", $id, "i")
            ->execute();
    }
}
