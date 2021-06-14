<?php
namespace App\Controller;

require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Exception;
use Firebase\JWT\ExpiredException;

class Token
{
    private static $key = "JasonWebToken";
    
    public static function CrearJWT($usuario)
    {
        $payload = array(
            'id' => $usuario->id,
            'mail' => $usuario->mail,
            'perfil' => $usuario->tipo_perfil,
        );

        return json_encode(JWT::encode($payload, self::$key,"HS256"));
    }

    public static function ObtenerPayLoad($token)
    {
        if (empty($token) || $token == null) {
            throw new Exception("El token esta vacio.");
        }
        return JWT::decode($token, self::$key, "HS256");
    }

    public static function Verificar($token){
        if(empty($token)|| $token=="")
            throw new Exception("El token esta vacio.");
        try {
            $decodificado = JWT::decode(
            $token,
            self::$key,
            "HS256"
            );
        } catch (ExpiredException $e){
           throw new Exception("Clave fuera de tiempo");
        }
    }

}