<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlumbingJobs extends Model
{
    protected $primaryKey = "idpljobs";
    protected $table = 'plumbing_jobs';
    public $timestamps = false;
}