<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtSessionReport extends Model
{
    use HasFactory;

    // اسم الجدول
    protected $table = 'court_session_reports';

    // الحقول المسموح نعمل لها mass assignment
    protected $fillable = [
        'court_case_id',
        'case_session_id',
        'participant_id',
        'name',
        'role',
        'statement_text',
        'fingerprint',
        'report_text',
        'decision_text',
        'report_mode',
    ];

    /*
     * العلاقات
     */

    // 🔹 الجلسة المرتبط بها المحضر
    public function session()
    {
        return $this->belongsTo(CaseSession::class, 'case_session_id');
    }

    // 🔹 القضية المرتبط بها المحضر
    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'court_case_id');
    }

    // 🔹 الطرف (مدعي / مدعى عليه) – اختياري
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}