<?php  

// Import required classes
use Public\Modules\speedyvisuals\SPVController;
use EvoPhp\Api\Requests\Requests;

// Functional modules from SpeedyVisuals
use Public\Modules\speedyvisuals\Classes\Subscriptions\Subscription;
use Public\Modules\speedyvisuals\Classes\Subscriptions\Addon;
use Public\Modules\speedyvisuals\Classes\Project\Proposal;
use Public\Modules\speedyvisuals\Classes\Project\ProjectAssignment;
use Public\Modules\speedyvisuals\Classes\Project\Message;

require_once "Public/Modules/speedyvisuals/Routes/Users.php";
require_once "Public/Modules/speedyvisuals/Routes/Projects.php";
require_once "Public/Modules/speedyvisuals/Routes/Portfolio.php";

// API ROUTE GROUP FOR SPEEDYVISUALS
$router->group('/speedyvisuals/api', function() use ($router) {

    // -----------------------
    // SUBSCRIPTION ENDPOINT
    // -----------------------

    // Subscribe a user to a plan
    $router->post('/subscription/new', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new Subscription)->createSubscription(...$params));
    });

    // -----------------------
    // ADDON ENDPOINT
    // -----------------------

    // Assign an addon to a user
    $router->post('/addon/assign', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new Addon)->assignAddon(...$params));
    });

    // -----------------------
    // PROPOSAL ENDPOINTS
    // -----------------------

    // Submit a new proposal
    $router->post('/proposal/new', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new Proposal)->create($params));
    });

    // Fetch proposals for a specific project
    $router->get('/proposal/project/{project_id}', function($params) {
        return (new Proposal)->getByProject($params['project_id']);
    });

    // -----------------------
    // PROJECT ASSIGNMENT ENDPOINTS
    // -----------------------

    // Assign a freelancer to a project
    $router->post('/assignment/assign', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new ProjectAssignment)->assign(...$params));
    });

    // Mark project as completed
    $router->post('/assignment/complete/{project_id}', function($params) {
        return (new ProjectAssignment)->markCompleted($params['project_id']);
    });

    // -----------------------
    // MESSAGE ENDPOINTS
    // -----------------------

    // Send a direct message
    $router->post('/message/send', function() {
        $request = new Requests;
        $params = json_decode(file_get_contents('php://input'), true);
        $request->evoAction($params)->auth()->execute(fn() => (new Message)->send($params));
    });

    // Fetch message thread between two users for a project
    $router->get('/message/conversation/{u1}/{u2}/{project_id}', function($params) {
        return (new Message)->getConversation($params['u1'], $params['u2'], $params['project_id']);
    });
});


// -----------------------
// PAGE ROUTES
// -----------------------

// General/public dashboard
$router->get('/spv', function($params){
    $controller = new SPVController;
    $controller->{'SPVMain/index'}($params)->auth()->template('pub')->setData(["pageTitle" => "Public"]);
});

// Admin dashboard
$router->get('/spv/a', function($params){
    $controller = new SPVController;
    $controller->{'SPVAdmin/index'}($params)->auth(2,3,4)->setData(["pageTitle" => "Admin"]);
});

// Buyer dashboard
$router->get('/buyer', function($params){
    $controller = new SPVController;
    $controller->{'SPVBuyer/index'}($params)->auth(14)->setData(["pageTitle" => "Buyer"]);
});

// Seller dashboard (shares buyer view logic)
$router->get('/seller', function($params){
    $controller = new SPVController;
    $controller->{'SPVBuyer/index'}($params)->auth(13)->setData(["pageTitle" => "Buyer"]);
});
