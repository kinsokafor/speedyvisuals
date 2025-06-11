<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/speedyvisuals/api/user', function() use ($router) {

    // -----------------------
    // USERS ENDPOINTS
    // -----------------------

    // Create a new project
    $router->post('/', function ($params) {
        $request = new Requests;
        $params = array_merge($params, (array) json_decode(file_get_contents('php://input'), true));
        $request->user($params)->auth();
    });
});