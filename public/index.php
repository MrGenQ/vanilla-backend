<?php
require "../bootstrap.php";
use Src\Controller\UserController;
use Src\Controller\PokeController;
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Accept: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// all of our endpoints start with /api
// everything else results in a 404 Not Found
if ($uri[1] !== 'api') {
    $response['body'] = '';
    return $response;
}

$action = null;
if (isset($uri[2])) {
    $action = $uri[2];
}

$id = null;
if (isset($uri[3])) {
    $id = (int) $uri[3];
}
$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method and user ID to the UserController and process the HTTP request:
if($uri[2] === 'register' || $uri[2] === 'login' || $uri[2] === 'logout' || $uri[2] === 'user' || $uri[2] === 'user-by-email' || $uri[2] === 'update-user' || $uri[2] === 'user-info' || $uri[2] === 'user-import'){
    $Controller = new UserController($dbConnection, $requestMethod, $action, $id);
    $Controller->processRequest();
}
else{
    $Controller = new PokeController($dbConnection, $requestMethod, $action, $id);
    $Controller->processRequest();
}
