<?php
namespace App\Controller;
require_once "./models/Venta.php";

use App\Models\Usuario;
use App\Models\Criptomoneda;
use App\Models\Venta as Venta;
use Illuminate\Support\Facades\Date;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VentaController
{
    public function AltaDeVenta(Request $request, Response $response, $args) : Response
    {
        $parametros = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();

        
        $idCliente = $parametros['id_cliente'];
        $idCripto = $parametros['id_cripto'];
        $cantidad = $parametros['cantidad'];

        $nombre = $archivos["foto"]->getClientFilename();

        $destino = __DIR__ ."/../FotosCripto/";
        $extension = explode(".",$nombre);
        //manejo foto:
        if(self::ValidarImagen($extension[1]))
        {
            $clt = new Usuario();
            $cliente = $clt::where('id', $idCliente)->first();
            $crp = new Criptomoneda();
            $cripto = $crp::where('id', $idCripto)->first();
            $nuevoNombre = explode("@",$cliente->mail)[0] ."-" .$cripto->nombre .date("Y-m-d") ."." .$extension[1];
            $archivos["foto"]->MoveTo( $destino .$nuevoNombre);



            // Creamos Venta
            $vt = new Venta();
            $vt->id_cliente = $idCliente;
            $vt->id_cripto = $idCripto;
            $vt->fecha = date("Y-m-d G:i:s");
            $vt->cantidad = $cantidad;
            $vt->foto_venta = $nuevoNombre;

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

    public function ReturnVentasAlemania_Fecha(Request $request, Response $response, $args) : Response
    {
        
        $fecha1 = "2021-06-10";
        $fecha2 = "2021-06-14";

        $crp = new Criptomoneda();
        $cripto = $crp::where("nacionalidad","Alemana")->first();

        $vt = new Venta();
        $ventas = $vt::where('id_cripto',$cripto->id)->whereBetween("fecha",[$fecha1,$fecha2])->get();

        $payload = json_encode(array("lista" => $ventas));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarImagen($fileType)
    {
        $retorno = false;
        if($fileType == "jpg" || $fileType =="jpeg" || $fileType == "png" || 
            $fileType == "JPG" || $fileType =="JPEG" || $fileType == "PNG" )
        {
            $retorno = true; 
        }
        return $retorno;
    }
}