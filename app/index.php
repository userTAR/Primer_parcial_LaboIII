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
require_once './controllers/VentaConstroller.php';
/* require_once "./controllers/ConsultaController.php"; */

use \App\Middleware\AdminMiddleware;
use \App\Middleware\AutentificadorMW;
use \App\Middleware\VendedorMiddleware;
use \App\Controller\CriptomonedaController;
use \App\Controller\UsuarioController;
use \App\Controller\VentaController;
use \App\Controller\Consultas;

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
//1
$app->post('/altaUsuario', UsuarioController::class .':Alta');
$app->post('/verificarUsuario', UsuarioController::class . ':VerificarUsuario');
$app->group('/criptomoneda', function (RouteCollectorProxy $group){
    //2
    $group->post('/alta', CriptomonedaController::class . ':Alta')->add(new AutentificadorMW("Admin")); // falta la parte de JWT ACA
    //3
    $group->get('/listarTodo', CriptomonedaController::class .':TraerTodos'); 
    //4
    $group->get('/listadoTipo/{nacionalidad}', CriptomonedaController::class .':TraerPorNacionalidad');
    //5
    $group->get('/traerUna/{id_Criptomoneda}', CriptomonedaController::class .':TraerUno')->add(new AutentificadorMW("Cliente"));
    //6
    /* $group->delete('/borrarUna/{id_Criptomoneda}', CriptomonedaController::class .':BorrarUno')->add(new AutentificadorMW("Administrador"));
    //8
    $group->put('/modificar', CriptomonedaController::class . ':ModificarUno')->add(new AutentificadorMW("Vendedor")); */   
});
//10
$app->group('/venta', function (RouteCollectorProxy $group){
    //7
    $group->post('/alta', VentaController::class . ':AltaDeVenta')->add(new AutentificadorMW("Cliente"));
    //a
    /* $group->get('/traerVentas/{id_empleado}', Consultas::class . ':VentasEmpleado');
    //b
    $group->get('/criptomonedaMasVendida', Consultas::class .':CriptomonedaMasVentas'); */
});
//11
/* $app->get('/criptomonedaPdf/{id_Criptomoneda', Consultas::class . ':GenerarPdfPorID');
 */

$app->run();