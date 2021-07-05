<?php
namespace App\Controller;
require_once __DIR__  ."/../models/Criptomoneda.php";
require_once __DIR__ ."/../controllers/VentaController.php";

use App\Controller\VentaController;
use App\Models\Criptomoneda as Criptomoneda;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CriptomonedaController
{

    //modificar la parte de la imagen
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();

        $precio = $parametros['precio'];
        $nombre = $parametros['nombre'];
        $nacionalidad = $parametros['nacionalidad'];
        
        //recuperacion de foto y seteado de path
        $nombreViejo = $archivos["foto"]->getClientFilename();
        $destino = __DIR__ ."/../fotos/";
        $extension = explode(".",$nombreViejo);

        // Creamos Criptomoneda
        $hr = new Criptomoneda();
        
        //manejo foto:
        if(VentaController::ValidarImagen($extension[1]))
        {
            $nuevoNombre = $nombre."." .$extension[1];
            $archivos["foto"]->MoveTo( $destino .$nuevoNombre);
            $hr->foto = $nuevoNombre;
        }
        
        $hr->precio = $precio;
        $hr->nombre = $nombre;
        $hr->nacionalidad = $nacionalidad;

        if($hr->save())
            $payload = json_encode(array("mensaje" => "Exito en el guardado de Criptomoneda"));
        else
            $payload = json_encode(array("mensaje" => "Error en el guardado de Criptomoneda"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Criptomoneda::all();
        $payload = json_encode(array("listaCriptomoneda" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPornacionalidad($request, $response, $args)
    {
        $nacionalidad = $args['nacionalidad'];

        $listanacionalidad = Criptomoneda::where('nacionalidad', '=', $nacionalidad)->get();

        $payload = json_encode(array("listaCriptomoneda" => $listanacionalidad));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Criptomoneda por id
        $id_hr = $args['id_Criptomoneda'];
        
        $hr = Criptomoneda::where('id' ,'=', $id_hr)->first();

        $payload = json_encode($hr);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function ModificarUno(Request $request, Response $response, $args) : Response
    {
        

        $parametros = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();
        
        $id = $parametros["id"];
        $nombre = $parametros["nombre"];
        $precio = $parametros["precio"];
        $nacionalidad = $parametros["nacionalidad"];
        
        
        $hr = new Criptomoneda();
        $cripto = $hr->find($id);
        $cripto->nombre = $nombre;
        $cripto->nacionalidad = $nacionalidad;
        $cripto->precio = $precio;
        
        if($archivos != null)
        {
            //recuperacion de foto y seteado de path
            $nombreViejo = $archivos["foto"]->getClientFilename();
            $destino = __DIR__ ."/../fotos/";
            $extension = explode(".",$nombreViejo);
            $nuevoNombre = $nombre."." .$extension[1];

            if(VentaController::ValidarImagen($extension[1]))
            {
                //si la foto ya existe se cambia al backup
                if(file_exists(__DIR__ ."/../fotos/" .$nuevoNombre))
                {
                    rename(__DIR__ ."/../fotos/" .$nuevoNombre, __DIR__ ."/../fotos/backup/" .$nuevoNombre);
                    $archivos["foto"]->MoveTo( $destino .$nuevoNombre);
                }
                //si no, se mueve y se deja la anterior en el mismo repo
                else
                {
                    $archivos["foto"]->MoveTo( $destino .$nuevoNombre);
                }
            }
        }

        if($cripto->save())
            $payload = json_encode(array("mensaje" => "Éxito en la modificación del Criptomoneda"));
        else
            $payload = json_encode(array("mensaje" => "Error en la modificación del Criptomoneda"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $criptomonedaId = $args["id_criptomoneda"];

        $hr = Criptomoneda::find($criptomonedaId);
        if($hr->delete())
            $payload = json_encode(array("mensaje" => "Criptomoneda borrado con exito"));
        else
            $payload = json_encode(array("mensaje" => "Error en el borrado de la criptomoneda"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
