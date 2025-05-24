<?php  

use Public\Modules\speedyvisuals\SPVController;
use EvoPhp\Api\Requests\Requests;

//API End points

//Pages

$router->get('/spv', function($params){
    $controller = new SPVController;
    $controller->{'SPVMain/index'}($params)->auth()->setData(["pageTitle" => "Public"]);
});

$router->get('/spv/a', function($params){
    $controller = new SPVController;
    $controller->{'SPVAdmin/index'}($params)->auth(2,3,4)->setData(["pageTitle" => "Admin"]);
});

$router->get('/buyer', function($params){
    $controller = new SPVController;
    $controller->{'SPVBuyer/index'}($params)->auth(14)->setData(["pageTitle" => "Buyer"]);
});

$router->get('/seller', function($params){
    $controller = new SPVController;
    $controller->{'SPVBuyer/index'}($params)->auth(13)->setData(["pageTitle" => "Buyer"]);
});