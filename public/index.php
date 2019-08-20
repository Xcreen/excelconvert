<?php

use DI\Container;
use ExcelConvert\Controller\HomeController;
use ExcelConvert\Controller\IEController;
use ExcelConvert\Controller\JobController;
use ExcelConvert\Handler\HttpErrorHandler;
use ExcelConvert\Views\Extension\TwigBaseURLExtension;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;

require __DIR__ . '/../vendor/autoload.php';

//Config
$displayErrorDetails = true;

//Setup DI-Container
$container = new Container();
AppFactory::setContainer($container);

//Create App
$app = AppFactory::create();

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

//Set ErrorMiddleware
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

//Register DI-Service
$container->set('twigservice', function () {
    $twig = new Twig('../src/Views', [
        'cache' => '../var/cache',
        //'cache' => false,
        'auto_reload' => true,
        'debug' => true
    ]);
    $twig->addExtension(new DebugExtension());
    $twig->addExtension(new TwigBaseURLExtension());
    return $twig;
});
//Register PDO-Service
$container->set('pdoservice', function () {
    return new PDO('mysql:host=localhost;dbname=excelconvert', 'excelconvert', 'excelconvert');
});

//Register Routes
$app->get('/', HomeController::class . ':index');
$app->get('/ie-error', IEController::class . ':index');
$app->get('/share/{public_job_id}', HomeController::class . ':showDownloadPage');
$app->get('/api/download/{public_job_id}', JobController::class . ':download');
$app->post('/api/upload', JobController::class . ':upload');
$app->get('/api/status/{public_job_id}', JobController::class . ':status');

//Start App
$app->run();
