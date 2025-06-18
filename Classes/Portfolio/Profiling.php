<?php

namespace Public\Modules\speedyvisuals\Classes\Portfolio;

use EvoPhp\Resources\User;
use EvoPhp\Database\Session;

final class Profiling
{
    public function __construct() {}

    public static function becomeASeller($data) {
        
        $session = Session::getInstance();
        if(!($userSession = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }
        $data["role"] = "seller";
        $user = new User;
        $res = $user->update($userSession->user_id, $data);
        if($res) {
            return $user::reAuth();
        } else {
            http_response_code(401);
            return "Request failed";
        }
    }
}