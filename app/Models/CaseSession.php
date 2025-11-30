<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
    

class CaseSession extends Model
{
    use HasFactory;

    protected $table = 'case_sessions';

    protected $fillable = [
        'court_case_id',
        'judge_id',
        'session_date',
        'session_time',
        'session_type',
        'status',
        'final_decision',
        'postponed_reason',
        'action_done',
        'judgment_type',
        'created_by',
        'session_goal',
    ];

    protected $casts = [
        'session_date' => 'datetime',
        'session_time' => 'datetime:H:i:s',
        'action_done' => 'boolean',
    ];

    // علاقات ممكن تضيفيها لاحقًا حسب الجداول المرتبطة:
    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'court_case_id');
    }

   public function judge()
   {
    return $this->belongsTo(User::class, 'judge_id');
   }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function reports()
{
    return $this->hasMany(CourtSessionReport::class, 'case_session_id');
}
}