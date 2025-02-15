<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTaskEvaluationRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_task_evaluation_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
    ];

    public function evaluation()
    {
        return $this->belongsTo(DailyTaskEvaluation::class, 'daily_task_evaluation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
