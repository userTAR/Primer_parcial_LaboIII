<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criptomoneda extends Model
{
    use SoftDeletes;

    /* Primary key definida por default en "id"*/
    /* Id autoincremental seteado true por default */
    public $timestamps = false;
    protected $fillable = [
        'precio','nombre','foto','nacionalidad'
    ];

}