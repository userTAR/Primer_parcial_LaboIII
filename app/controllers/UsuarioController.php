<?php
namespace App\Controller;
require_once "./models/Usuario.php";
require_once 'TokenController.php';

use Firebase\JWT\JWT;
use App\Models\Usuario as Usuario;
use \App\Controller\Token as TKN;
use App\Models\Venta;
use App\Models\Criptomoneda;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;



class UsuarioController
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mail = $parametros['mail'];
        $tipo = $parametros['tipo'];
        $clave = $parametros['clave'];


        if($tipo === "admin" || $tipo === "cliente")
        {
            // Creamos Usuario
            $usr = new Usuario();
            $usr->mail = $mail;
            $usr->tipo = $tipo;
            $usr->clave = $clave;
            if($usr->save())
                $payload = json_encode(array("mensaje" => "Exito en el guardado de Usuario"));
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado de Usuario"));
        }
        else
            $payload = json_encode(array("mensaje" => "Error, tipo ingresado incorrecto"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarUsuario($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $mail = $params["mail"];
        $tipo = $params["tipo"];
        $clave = $params["clave"];

        $usr = Usuario::where('mail', $mail)->first();

        if($tipo == $usr->tipo && $clave == $usr->clave)
            $payload = json_encode(array("mensaje" => "OK", "perfil" => $usr->tipo, "token" => TKN::CrearJWT($usr)));
        else
            $payload = json_encode(array("mensaje" => "Usuario Incorrecto"));

        $response->getBody()->write($payload);  
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ReturnUsuarios_Compraron_Eterium(Request $request, Response $response, $args) : Response
    {
        $nombreCripto = $args["nombre_cripto"];
        $retorno = array();

        $crp = new Criptomoneda();
        $cripto = $crp::where("nombre",$nombreCripto)->first();

        $vt = new Venta();
        $ventas = $vt::where('id_cripto',$cripto->id)->get();

        $usr = new Usuario();

        foreach($ventas as $venta)
        {
            $usuario = $usr::where("id",$venta->id_cliente)->get();
            array_push($retorno,$usuario);
        }        
        $retornoFiltrado = array_unique($retorno,SORT_REGULAR);
        $payload = json_encode($retornoFiltrado);

        $response->getBody()->write($payload);  
        return $response->withHeader('Content-Type', 'application/json');
    }
}