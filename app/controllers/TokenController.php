<?php
namespace App\Controller;



use Exception as GlobalException;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class Token
{
    private static $password = "passJWT";
    private static $encode = array('HS256');
    
    public static function CrearJWT($usuario)
    {
        $data = array("mail" => $usuario->mail, "tipo" => $usuario->tipo);
        $time = time();
        $payload = array(
            'iat'=> $time,
            'exp'=> $time + (20*60),
            'data' => $data,    
        );
        return JWT::encode($payload,self::$password,self::$encode[0]);
    }

    public static function Verificar($token){
        if(empty($token)|| $token=="")
            throw new GlobalException("El token esta vacio.");
        try
        {
            $decodificado = self::ObtenerDatos($token);
        }
        catch (ExpiredException $e)
        {
            throw new GlobalException("Clave fuera de tiempo");
        }
        catch (SignatureInvalidException $e)
        {
            throw new GlobalException("Token incorrecto");
        }
        return $decodificado;
    }

    private static function ObtenerDatos($token){
        return JWT::decode($token, self::$password, self::$encode)->data;
    }

}