<?php
use EvoPhp\Api\Requests\Requests;

use Public\Modules\speedyvisuals\Classes\Project\Project;
use EvoPhp\Database\Session;

$router->group('/speedyvisuals/api/project', function() use ($router) {

    // -----------------------
    // PROJECT ENDPOINTS
    // -----------------------

    // Create a new project
    $router->post('/new', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => Project::new($params));
    });

    // Fetch all projects by a user
    $router->get('/user/{user_id}', function($params) {
        $request = new Requests;
        $request->evoAction($params)->auth()->execute(fn() => (new Project)->getAllByUser($params['user_id']));
    });

    $router->get('/mine', function($params) {
        $session = Session::getInstance();

        if(!($user = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }

        $request = new Requests;
        $request->evoAction($params)->auth()->execute(fn() => (new Project)->getAllByUser($user->user_id));
    });

    // Search/filter projects
    $router->post('/search', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new Project)->search($params));
    });
});