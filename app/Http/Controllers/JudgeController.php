<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseSession;
use App\Models\User;
use Carbon\Carbon;
use App\Models\CourtSessionReport;
use App\Models\Participant;
use App\Models\CourtCase;

class JudgeController extends Controller
{
   public function index()
{
    // 🧑‍⚖️ القاضي الحالي
    $judge = auth()->user();

    // 📅 تاريخ اليوم
    $today = \Carbon\Carbon::today();

    // ✅ الجدول الأول: جلسات اليوم المرتبطة بالقاضي
    $sessions = \App\Models\CaseSession::with([
        'courtCase.tribunal',
        'courtCase.department'
    ])
    ->where('judge_id', $judge->id)
    ->whereDate('session_date', $today)
    ->get();

    // ✅ الجدول الثاني: القضايا المرتبطة بالقاضي، مع المشاركين والتوقيف والتبليغ والجلسات
    $cases = \App\Models\CourtCase::with([
        'participants',
        'arrestMemos',
        'notifications',
        'sessions' // ✅ تم إضافة تحميل الجلسات المرتبطة بالقضية
    ])
    ->where('judge_id', $judge->id)
    ->get();

    // 📤 إرسال البيانات للواجهة
    return view('clerk_dashboard.judge', compact('judge', 'sessions', 'cases'));
}





//هاض المحاضر صفحة القاضي
public function showTrialReport(CaseSession $session)
{
    // ✅ نفس اللوجيك تبع صفحة الطابعة تقريباً

    // القضية المرتبطة بالجلسة
    $case = $session->courtCase;

    // القاضي (المستخدم الحالي)
    $judge = auth()->user();

    // نستخدم نفس المتغير اللي متعودين عليه بالصفحة (اسمه typist)
    // عشان الـ Blade ما يعطي خطأ
    $typist = auth()->user();

    // الأطراف الأساسية في القضية
    $participants = $case->participants;

    // 🟦 نحمل فقط محاضر "محضر المحاكمة" لهاي الجلسة
    $reports = CourtSessionReport::where('case_session_id', $session->id)
                ->where('report_mode', 'trial')
                ->get();

    // الأطراف المضافة من داخل المحضر
    $added_parties = $reports->whereNull('participant_id')
                             ->whereNotNull('name');

    // المحضر العام (القرار النهائي)
    $session_report = $reports->whereNull('participant_id')
                              ->whereNotNull('decision_text')
                              ->first();

    // أقوال الأطراف الأساسية
    $statements = $reports->whereNotNull('participant_id');

    // ✅ مهم: نمرر كل المتغيرات اللي الصفحة تستخدمها
    return view('clerk_dashboard.trial_report', compact(
        'session',
        'case',
        'judge',
        'typist',
        'participants',
        'reports',
        'added_parties',
        'session_report',
        'statements'
    ));
}

public function showAfterTrialReport(CaseSession $session)
{
    $case = $session->courtCase;
    $judge = auth()->user();
    $typist = auth()->user();

    $participants = $case->participants;

    // 🟦 فقط محاضر "ما بعد"
    $reports = CourtSessionReport::where('case_session_id', $session->id)
                ->where('report_mode', 'after')
                ->get();

    $added_parties = $reports->whereNull('participant_id')
                             ->whereNotNull('name');

    $savedDecision = $reports->whereNotNull('decision_text')->first();

    return view('clerk_dashboard.after_trial_report', compact(
        'session',
        'case',
        'judge',
        'typist',
        'participants',
        'reports',
        'added_parties',
        'savedDecision'
    ));
}






































public function getTodayRequests()
{
    try {

        $judgeId = auth()->id(); // القاضي هو user مباشرة

        $requests = \App\Models\RequestSchedule::where('judge_id', $judgeId)
                        ->whereDate('session_date', today())
                        ->get();

        return response()->json([
            'requests' => $requests
        ]);

    } catch (\Exception $e) {

        // 🔥 تسجيل الخطأ في الـ log
        \Log::error("❌ Error in getTodayRequests: " . $e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'error' => 'Server error'
        ], 500);
    }
}

public function getAllRequests()
{
    try {

        $judgeId = auth()->id(); // القاضي هو user مباشرة

        $requests = \App\Models\RequestSchedule::where('judge_id', $judgeId)
                        ->get();

        return response()->json([
            'requests' => $requests
        ]);

    } catch (\Exception $e) {

        // 🔥 تسجيل الخطأ في الـ log
        \Log::error("❌ Error in getAllRequests: " . $e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'error' => 'Server error'
        ], 500);
    }
}
}