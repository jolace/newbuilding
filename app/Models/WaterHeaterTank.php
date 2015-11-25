<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterHeaterTank extends Model
{
     protected $table = 'water_heater_tank';
     public $timestamps = false;
     protected $primaryKey  = 'id_wht';
}
