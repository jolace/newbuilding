<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractFinalCorrection extends Model
{
    protected $table = 'contract_final_inspection_correction';
    protected $primaryKey  = 'cfrc';
    public $timestamps = false;
}
