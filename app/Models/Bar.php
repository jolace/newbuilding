<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
     protected $table = 'bar';
     public $timestamps = false;
     protected $primaryKey  = 'idBar';
}
