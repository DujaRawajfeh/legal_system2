<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestSchedule extends Model
{
   protected $fillable = [
    'request_number',
    'court_year',
    'session_date',
    'session_time',
    'session_type',
    'session_purpose',
    'session_status',
    'session_reason',
    'original_date',

    'tribunal_id',
    'judge_id',
    'department_id',

    'title',

    // التواريخ
    'judgment_date',
    'closure_date',

    // الأحكام العامة
    'judgment_text_final',
    'judgment_text_waiver',

    // الأحكام لكل طرف
    'judgment_text_plaintiff',
    'judgment_text_defendant',
    'judgment_text_third_party',
    'judgment_text_lawyer',

    // بيانات المشتكي
    'plaintiff_name',
    'plaintiff_national_id',
    'plaintiff_residence',
    'plaintiff_job',
    'plaintiff_phone',

    // بيانات المشتكى عليه
    'defendant_name',
    'defendant_national_id',
    'defendant_residence',
    'defendant_job',
    'defendant_phone',

    // بيانات الشاهد
    'third_party_name',
    'third_party_national_id',
    'third_party_residence',
    'third_party_job',
    'third_party_phone',

    // بيانات المحامي
    'lawyer_name',
    'lawyer_national_id',
    'lawyer_residence',
    'lawyer_job',
    'lawyer_phone',
];



public function judge()
{
    return $this->belongsTo(User::class, 'judge_id');
}

public function tribunal()
{
    return $this->belongsTo(Tribunal::class);
}

public function department()
{
    return $this->belongsTo(Department::class);
}
}
