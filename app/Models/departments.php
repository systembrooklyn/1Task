<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departments extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentsFactory> */
    use HasFactory;
    protected $fillable = [
        'name'
    ];
}
