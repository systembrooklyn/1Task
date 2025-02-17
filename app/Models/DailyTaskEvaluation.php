<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DailyTaskEvaluation extends Model
{
    use HasFactory;
    protected $fillable = [
        'daily_task_id',
        'user_id',
        'comment',
        'rating',
        'label'
    ];
    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function revisions()
    {
        return $this->hasMany(DailyTaskEvaluationRevision::class, 'daily_task_evaluation_id');
    }
}
