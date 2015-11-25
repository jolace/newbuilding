<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingDescription extends Model
{
     protected $table = 'building_description';     
     public $timestamps = false;
     protected $primaryKey  = 'idbuilding';
}
