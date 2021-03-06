<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'stopNonAnton.php';

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "slimtest";


$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();


//add logger to the dependency injection controller

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

//add db to the dependency injection controller

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

//$container->get('logger')->addInfo("Just checking if Monolog logger actually works");


//adding routes

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $sql = "INSERT INTO greetedNames (name) VALUES ('$name')";
    $this->db->query($sql);
    $response->getBody()->write("Ohh Hello, $name, I can only greet you my master!");
    return $response;
})->add(new stopNonAnton());


$app->get('/', function (Request $request, Response $response) {
    $this->logger->addInfo("Home loaded.");
    $statement = $this->db->prepare("SELECT name FROM greetedNames");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_COLUMN);
    print_r($result);
    $response->getBody()->write("Hello. This is home.");
    return $response;
})->setName('home');

$app->run();
