<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTaskCounter extends Model
{
    protected $table = 'daily_task_counters';
    protected $primaryKey = 'company_id';
    public $incrementing = false;
    protected $fillable = [
        'company_id',
        'last_daily_task_no',
    ];
}
