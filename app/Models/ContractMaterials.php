<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractMaterials extends Model
{
    protected $table = 'contract_materials';
    protected $primaryKey  = 'idcm';
    public $timestamps = false;
}
