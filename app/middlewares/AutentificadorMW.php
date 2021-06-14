<?php
namespace App\Middleware;

require_once "./vendor/autoload.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
use App\Controller\Token as TKN;
use Exception;

class AutentificadorMW
{
    private static $tipo;

    public function __construct($tipo)
    {
        self::$tipo = $tipo;
    }
    public function __invoke(Request $request, RequestHandler $handler) : ResponseMW
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $aux = null;
        try
        {
            $payload = TKN::ObtenerPayLoad($token);
        }
        catch (Exception $e)
        {
            $aux['flag'] = true;
            $aux['mensaje'] = $e->getMessage();
        }
        if($payload['perfil'] == self::$tipo)
            $response = $handler->handle($request);
        else
        {
            $response = new ResponseMW();
            //token no valido
            if($aux['flag'] == true)
                $payload = json_encode(array('mensaje' => $aux['mensaje']));
            //token valido pero sin permiso
            else
                $payload = json_encode(array("mensaje" => "Acceso Denegado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}