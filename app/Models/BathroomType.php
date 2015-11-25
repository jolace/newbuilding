<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BathroomType extends Model
{
     protected $table = 'bathroom_type';
     public $timestamps = false;
     protected $primaryKey  = 'idbathroom_type';
}


//  bath_type   enum('sink1', 'sink2', 'bidet', 'toilet')
//  type    enum('wall', 'exposed')
//  side    enum('fixture', 'back') 
//  comment varchar(150) 
//  group_field int(11)  
//  job_id