<?php
namespace App\Controller;
require_once "./models/Criptomoneda.php";

use \app\Models\Criptomoneda as Criptomoneda;

class CriptomonedaController
{
    public function Alta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $precio = $parametros['precio'];
        $nombre = $parametros['nombre'];
        $nacionalidad = $parametros['nacionalidad'];

        //manejo foto:
        $destino = "./resources/fotosCriptomonedas/" .$nombre .pathinfo($parametros['foto'],PATHINFO_EXTENSION);
        move_uploaded_file($parametros['foto']['tmp_name'], $destino);



        // Creamos Criptomoneda
        $hr = new Criptomoneda();
        $hr->precio = $precio;
        $hr->nombre = $nombre;
        $hr->foto = $destino;
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

    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros["id"];
        $nombre = $parametros["nombre"];
        $precio = $parametros["precio"];
        $nacionalidad = $parametros["nacionalidad"];
        $foto = isset($parametros["foto"]) ? $parametros['foto'] : null;

        $hr = Criptomoneda::find($id);
        $hr->nombre = $nombre;
        $hr->nacionalidad = $nacionalidad;
        $hr->precio = $precio;
        if($foto != null)
        {
            $path = "./resources/fotosCriptomonedas/" .$nombre .pathInfo($foto, PATHINFO_EXTENSION);
            if(file_exists($path))
            {
                rename($path, "./resources/fotosCriptomonedas/backup/" .$nombre .pathinfo($foto,PATHINFO_EXTENSION));
                move_uploaded_file($foto['tmp_name'],$path);
                $hr->foto = $path;
            }
            else
            {
                unlink($hr->foto);
                move_uploaded_file($foto['tmp_name'],$path);
                $hr->foto = $path;
            }
        }

        if($hr->save())
            $payload = json_encode(array("mensaje" => "Éxito en la modificación del Criptomoneda"));
        else
            $payload = json_encode(array("mensaje" => "Error en la modificación del Criptomoneda"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $CriptomonedaId = $parametros['id_Criptomoneda'];

        $hr = Criptomoneda::find($CriptomonedaId);
        $hr->delete();

        $payload = json_encode(array("mensaje" => "Criptomoneda borrado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
