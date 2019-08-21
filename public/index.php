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

//Load Configuration
$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

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
$errorMiddleware = $app->addErrorMiddleware(getenv('SHOW_PHP_ERROR'), false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

//Register DI-Service
$container->set('twigservice', function () {
    $twigCache = getenv('TWIG_CACHE_PATH') !== 'false' ? getenv('TWIG_CACHE_PATH') : false ;

    $twig = new Twig('../src/Views', [
        'cache' => $twigCache,
        'auto_reload' => filter_var(getenv('TWIG_AUTO_RELOAD'), FILTER_VALIDATE_BOOLEAN),
        'debug' => filter_var(getenv('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN)
    ]);
    $twig->addExtension(new DebugExtension());
    $twig->addExtension(new TwigBaseURLExtension());
    return $twig;
});
//Register PDO-Service
$container->set('pdoservice', function () {
    return new PDO('mysql:host=' . getenv('MYSQL_HOST') . ';port=' . getenv('MYSQL_PORT') . ';dbname=' . getenv('MYSQL_DATABASE'),
        getenv('MYSQL_USERNAME'), getenv('MYSQL_PASSWORD'));
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
