<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatchingJobs extends Model
{
    protected $primaryKey = "idpatchingjob";
    protected $table = 'patching_jobs';
    public $timestamps = false;
}