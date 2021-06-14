<?php
namespace App\Controller;
require_once "./models/Usuario.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use \app\Models\Usuario as Usuario;
use \App\Controller\Token as TKN;


class UsuarioController
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mail = $parametros['mail'];
        $tipo = $parametros['tipo'];
        $clave = $parametros['clave'];



        // Creamos Usuario
        $usr = new Usuario();
        $usr->mail = $mail;
        $usr->tipo = $tipo;
        $usr->clave = $clave;

        if($usr->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado de Usuario"));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado de Usuario"));
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
}