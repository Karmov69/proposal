<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class calculation extends Model
{
    protected $table = 'calculations';

    protected $fillable = [
        'name',
        'user_id',
        'user_name',
        'user_phone',
        'cost_per_hour',
        'template_id',
        'tasks',
        'info',
        'additional_tasks'
    ];
}