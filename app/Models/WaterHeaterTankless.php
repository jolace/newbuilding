<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterHeaterTankless extends Model
{
     protected $table = 'water_heater_tankless';
     public $timestamps = false;
     protected $primaryKey  = 'id_whtl';
}
