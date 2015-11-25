<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterHeater extends Model
{
     protected $table = 'water_heater';
     public $timestamps = false;
     protected $primaryKey  = 'id_wh';
}
