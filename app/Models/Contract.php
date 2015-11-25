<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contract';
    protected $primaryKey  = 'contract_id';
    public $timestamps = false;
}
