<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractPermit extends Model
{
    protected $table = 'contract_permit';
    protected $primaryKey  = 'idcp';
    public $timestamps = false;
}
