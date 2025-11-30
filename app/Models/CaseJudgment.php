<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseJudgment extends Model
{
    protected $fillable = [
    'court_case_id',
    'participant_id',
    'judgment_mode',
    'judgment_date',
    'closure_date',
    'judgment_type',
    'charge_decision',
    'charge_text',
    'execution_details',
    'termination_type',
    'judgment_summary',
    'charge_split_type',
    'created_by',
];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
