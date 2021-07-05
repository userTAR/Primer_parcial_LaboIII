<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Illuminate\Database\Capsule\Manager as Capsule;


require_once '../vendor/autoload.php';
require_once "./middlewares/AutentificadorMW.php";
require_once './controllers/UsuarioController.php';
require_once './controllers/CriptomonedaController.php';
require_once "./controllers/VentaController.php";
require_once "./controllers/ConsultaController.php";


use \App\Middleware\AutentificadorMW;
use \App\Controller\CriptomonedaController;
use \App\Controller\UsuarioController;
use \App\Controller\VentaController;
use App\Models\Venta;
use App\Controller\Consultas;

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
/* $app->setBasePath('/app'); */

// Add error middleware
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();


// Routes
$app->post('/altaUsuario', UsuarioController::class .':Alta');
//1
$app->post('/verificarUsuario', UsuarioController::class . ':VerificarUsuario');

$app->group('/criptomoneda', function (RouteCollectorProxy $group){
    //2
    $group->post('/alta', CriptomonedaController::class . ':Alta')->add(new AutentificadorMW("admin"));
    //3
    $group->get('/listarTodo', CriptomonedaController::class .':TraerTodos'); 
    //4
    $group->get('/listadoTipo/{nacionalidad}', CriptomonedaController::class .':TraerPorNacionalidad');
    //5
    $group->get('/traerUna/{id_criptomoneda}', CriptomonedaController::class .':TraerUno')->add(new AutentificadorMW("cliente"));
    //9
    $group->delete('/borrarUna/{id_criptomoneda}', CriptomonedaController::class .':BorrarUno')->add(new AutentificadorMW("admin"));
    //10
    $group->put('/modificar', CriptomonedaController::class . ':ModificarUno')->add(new AutentificadorMW("admin"));   
});
$app->group('/venta', function (RouteCollectorProxy $group){
    //6
    $group->post('/alta', VentaController::class . ':AltaDeVenta')->add(new AutentificadorMW("cliente"));
    //7
    $group->get('/traerVentas/fecha/nacionalidad', VentaController::class . ':ReturnVentasAlemania_Fecha')->add(new AutentificadorMW("admin"));
    //8
    $group->get('/traerUsuarios/criptoEspecifica/{nombre_cripto}', UsuarioController::class .':ReturnUsuarios_Compraron_Eterium')->add(new AutentificadorMW("admin"));
});
$app->group('/pdf', function (RouteCollectorProxy $group){
    //11
    $group->get('/ventas', Consultas::class . ':ventasPdf')->add(new AutentificadorMW("admin"));
    //12
    $group->get('/mayorImporte', Consultas::class . ':PdfCriptoMayorImporte')->add(new AutentificadorMW("admin"));
    //13
    $group->get('/criptoMasTransacciones', Consultas::class . ':PdfCriptoMasTransacciones')->add(new AutentificadorMW("admin"));
});
//14
$app->post('/csv', Consultas::class . ':GenerarCSV')->add(new AutentificadorMW("admin"));

$app->run();