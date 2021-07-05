<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{

    /* Primary key definida por default en "id"*/
    /* Id autoincremental seteado true por default */
    public $timestamps = false;
    protected $fillable = [
        'id_cliente','id_cripto','fecha','cantidad','foto_venta'
    ];

}