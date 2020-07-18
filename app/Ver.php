<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ver extends Model
{
    protected $table = 'ver';
    protected $dates =  ['created_at', 'updated_at'];
    protected $fillable = ['id', 'version', 'min_version'];
}
