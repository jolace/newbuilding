<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractInspection extends Model
{
    protected $table = 'contract_inspection';
    protected $primaryKey  = 'idfi';
    public $timestamps = false;
}
