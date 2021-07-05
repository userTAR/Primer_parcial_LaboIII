<?php
namespace App\Controller;


require_once __DIR__ ."/../models/Venta.php";
require_once __DIR__ ."/../models/Criptomoneda.php";
require_once __DIR__ ."/../../vendor/autoload.php";

use App\Models\Criptomoneda;
use App\Models\Venta;
use Dompdf\Dompdf;


class Consultas
{
    public function VentasPdf($request,$response,$args)
    {
        $vnt = new Venta();
        $venta = $vnt::all();
        $encoded = json_encode($venta);

        self::GenerarPDF(json_decode($encoded));

    }
    public function PdfCriptoMayorImporte($request,$response,$args)
    {
        $crp = new Criptomoneda();
        $vnt = new Venta();
        $flag = true;
        $mayor = 0;
        $id = 0;

        $criptos = $crp::all();
        $ventas = $vnt::all();

        foreach ($ventas as $key => $venta) {
            foreach ($criptos as $key => $cripto) {
                if($venta->id_cripto == $cripto->id)
                {
                    $total = $venta->cantidad * $cripto->precio;
                    $array[$cripto->id] = $total;
                }
            }
        }
        foreach ($array as $key => $value) {
            if($value > $mayor || $flag == true)
            {
                $flag = false;
                $mayor = $value;
                $id = $key;
            }
        };
        $payload = $crp::where('id','=',$id)->first();

        self::GenerarPDF($payload);
    }

    public function PdfCriptoMasTransacciones($request,$response,$args)
    {
        $flag = true;
        $mayor = 0;
        $id = 0;
        $vnt = new Venta();
        $array = array();

        $ventas = $vnt::all()->countBy(function($venta){
            return $venta->id_cripto;
        });
        
        foreach ($ventas as $key => $value) {
            if($value > $mayor || $flag == true)
            {
                $flag = false;
                $mayor = $value;
                $id = $key;
            }
        }

        $crp = new Criptomoneda();
        $payload = $crp::where('id','=',$id)->first();
        array_push($array,$payload);
        $ventas = $vnt::where('id_cripto',$id)->get();
        foreach ($ventas as $key => $venta) {
            array_push($array,$venta);
        }

        $encoded = json_encode($array);
        
        self::GenerarPDF(json_decode($encoded));
    }

    private static function GenerarPDF($array)
    {
        $dompfdp = new Dompdf();
        $tabla = self::generarHTML($array);
        $dompfdp->setPaper('A4');
        $dompfdp->loadHtml($tabla);
        $dompfdp->render();
        $dompfdp->stream("ventas");
    }

    private static function generarHTML ( array $arrayDatos ) : string {
        $html = '<table style="border: 1px solid black;border-collapse: collapse;">';
        
        foreach ( $arrayDatos as $dato ) {
            
            $html .= '<tr style="border: 1px solid black;">';
            
            foreach ( $dato as $col ) {
                
                if ( $col instanceof \DateTimeInterface ) 
                $colEncoded = $col->format('Y-m-d H:i:s');
                else
                $colEncoded = json_encode($col, true);
                
                $html .= "<td style=\"border:1px solid black;\">$colEncoded</td>";
            }
            
            $html .= "</tr>";
        }
        
        $html .= "</table>";
        return $html;
    }

    public function GenerarCSV($request,$response,$args)
    {
        $parametros = $request->getParsedBody();

        $mail = $parametros["mail"];
        $tipo = $parametros["tipo"];
        $clave = $parametros["clave"];

        $array = self::LeerCsv();
        

        $archivo = fopen("./resources/usuarios.csv","w");
        //cabecera
        fputcsv($archivo,["mail","tipo","clave"]);
        //cuerpo

        array_push($array,[$mail,$tipo,$clave]);
        foreach ($array as $key => $value) {
            fputcsv($archivo,$value);
        }

        fclose($archivo);

        $payload = json_encode(array("mensaje" => "Guardado"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function LeerCsv()
    {
        $archivo = fopen("./resources/usuarios.csv","r");
        $retorno = array();
        $flag = true;

        while(($array = fgetcsv($archivo)) !== false)
        {
            if($flag == true)
            {
                $flag = false;
                continue;
            }
            array_push($retorno,[$array[0],$array[1],$array[2]]);
        }

        fclose($archivo);

        return $retorno;
    }
}