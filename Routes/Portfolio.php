<?php
use EvoPhp\Api\Requests\Requests;

use EvoPhp\Database\Session;
use Public\Modules\speedyvisuals\Classes\Portfolio\Profiling;

$router->group('/speedyvisuals/api/portfolio', function() use ($router) {

    // -----------------------
    // PORTFOLIO ENDPOINTS
    // -----------------------

    // Create a new project
    $router->post('/become-a-seller', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth(13)->execute(fn() => Profiling::becomeASeller($params));
    });

});
