<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
     protected $table = 'laundry';
     public $timestamps = false;
     protected $primaryKey  = 'idlaundry';
}
