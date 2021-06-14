<?php
namespace App\Controller;
require_once "./models/Venta.php";
require_once './interfaces/IApiUsable.php';

use App\Models\Usuario;
use \app\Models\Venta as Venta;

class VentaController
{
    public function AltaDeVenta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idCliente = $parametros['id_cliente'];
        $cantidad = $parametros['cantidad'];

        //manejo foto:
        if(self::ValidarImagen($parametros['foto'],PATHINFO_EXTENSION))
        {
            $clt = new Usuario();
            $cliente = $clt::where('id', '=', $idCliente)->first();
            $destino = "./FotosCripto/" .$cliente->nombre .date("d-m-o") .pathinfo($parametros['foto'],PATHINFO_EXTENSION);
            move_uploaded_file($parametros['foto']['tmp_name'], $destino);



            // Creamos Venta
            $vt = new Venta();
            $vt->id_cliente = $idCliente;
            $vt->fecha = date("d-m-o");
            $vt->cantidad = $cantidad;
            $vt->foto = $destino;

            if($vt->save())
                $payload = json_encode(array("mensaje" => "Exito en el guardado del Venta"));
            else
                $payload = json_encode(array("mensaje" => "Error en el guardado del Venta"));
        }
        else
            $payload = json_encode(array("mensaje" => "Error, imagen con extensión incorrecta"));
            
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    private static function ValidarImagen($fileType)
    {
        $retorno = false;
        if($fileType == "jpg" || $fileType =="jpeg" || $fileType == "png")
        {
            $retorno = true; 
        }
        return $retorno;
    }
}