<?php
namespace App\Middleware;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
use App\Controller\Token as TKN;
use Exception as GlobalException;

class AutentificadorMW
{
    private $tipo;

    public function __construct($tipo)
    {
        $this->tipo = $tipo;
    }
    public function __invoke(Request $request, RequestHandler $handler) : ResponseMW
    {
        $response = new ResponseMW();
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $aux = null;
        $aux['flag'] = false;
        try
        {
            $tokenVerificado = TKN::Verificar($token);
            if($tokenVerificado->tipo == $this->tipo)
                $aux['flag'] = true;
            else
                $payload = json_encode(array("mensaje" => "Acceso Denegado"));
            
        }
        catch (GlobalException $e)
        {
            $aux['mensaje'] = $e->getMessage();
            $payload = json_encode(array('mensaje' => $aux['mensaje']));
        }

        if($aux['flag'] != true)
        {
            $response->getBody()->write($payload);
            $response->withHeader('Content-Type', 'application/json');
        }
        else
            $response = $handler->handle($request);

        return $response;
    }
}