<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourtCase extends Model
{

    use HasFactory;

    protected $fillable = [
        'type',
        'number',
        'year',
        'tribunal_id',
        'department_id',
        'created_by',
        'judge_id',
    ];
    
    // المحكمة المرتبطة
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_id');
    }

    // القلم المرتبط
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // المستخدم الذي أنشأ القضية
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // الأطراف المرتبطة بالقضية (نضيف جدول participants لاحقًا)
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function judge()
{
    return $this->belongsTo(User::class, 'judge_id');
}

public function sessions()
{
    return $this->hasMany(CaseSession::class);
}

public function transfer()
{
    return $this->hasOne(CaseTransfer::class, 'target_case_id');
}

public function judgments()
{
    return $this->hasMany(CaseJudgment::class);
}



public function arrestMemos()
{
    return $this->hasMany(ArrestMemo::class, 'case_id');
}

public function notifications()
{
    return $this->hasMany(Notification::class, 'case_id');
}

public function archivedDocuments()
{
    return $this->hasMany(ArchivedDocument::class);
}


public function sessionReports()
{
    return $this->hasMany(CourtSessionReport::class, 'court_case_id');
}


public function caseJudgment()
{
    return $this->hasOne(CaseJudgment::class, 'court_case_id', 'id');
}
}

