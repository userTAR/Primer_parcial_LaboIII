<?php
namespace App\Controller;

require_once "./app/models/Hortaliza.php";
require_once "./app/models/Usuario.php";
require_once "./app/models/Venta.php";
require_once "../../vendor/autoload.php";

use App\Models\Hortaliza;
use App\Models\Usuario;
use App\Models\Venta;
use Dompdf\Dompdf;


class Consultas
{
    public function VentasEmpleado($request, $response, $args)
    {
        $idEmpleado = $args['id_empleado'];
        
        $usr = new Usuario();
        $vnt = new Venta();

        $match = $usr::where('id' , '=' ,$idEmpleado)->get();
        if(!$match->isEmpty())
            $payload = $vnt::where('id_empleado', '=', $match->id);
        else
            $payload = json_encode(array("mensaje" => "El empleado no tiene ventas"));
        
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function HortalizasMasVentas($request,$response,$args)
    {
        $vnt = new Venta();
        $flag = true;
        $mayor = 0;

        $ventas = $vnt::all()->countBy(function($venta)
        {
            return $venta->id_hortaliza;
        });
        foreach ($ventas as $key => $value) {
            if($value > $mayor || $flag == true)
            {
                $flag = false;
                $mayor = $value;
                $id = $key;
            }
        };
        $hrt = new Hortaliza();
        $payload = $hrt::where('id','=',$id)->first();     

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GenerarPDFPorID($request,$response,$args)
    {
        $id = $args['id_hortaliza'];

        $hrt = new Hortaliza();
        $hortaliza = $hrt::where('id','=',$id)->first();
        $tabla = self::GenerarTabla($hortaliza);
        $dompfdp = new Dompdf();
        $dompfdp->loadHtml($tabla);
        $dompfdp->setPaper('A4','landscape');
        $dompfdp->render();
        $dompfdp->stream("hortaliza_" .$id,);
    }

    public function GenerarCSVTodos($request,$response,$args)
    {
        $hrt = new Hortaliza();
        $lista = $hrt::all();
        $archivo = fopen("./resources/listadoHortalizas.csv","w");
        //cabecera
        fputcsv($archivo,array('id','precio','nombre','foto','tipo'));
        //cuerpo
        for ($i=0; $i < count($lista) ; $i++) { 
            $array = array();
            foreach ($lista as $value) {
                array_push($array,$value);
            }
            fputcsv($archivo,$array);
        }
    }

    private static function GenerarTabla($objeto)
    {
        $tabla = "<table>
                    <thead>
                    <tr>";
        foreach ($objeto as $key => $value) {
            $tabla .= `<th>$key</th>`;
        }
        $tabla .= `</tr>
                </thead>
                <tbody>
                <tr>`;
        foreach ($objeto as $key => $value) {
            $tabla .=`<td>$value</td>`;
        }
        $tabla .= `</tr>
                    </tbody>
                    </table>`;

        return $tabla;
    }
}