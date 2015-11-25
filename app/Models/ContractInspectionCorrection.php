<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractFinalCorrection extends Model
{
    protected $table = 'contract_inspection_correction';
    protected $primaryKey  = 'cfirc';
    public $timestamps = false;
}
