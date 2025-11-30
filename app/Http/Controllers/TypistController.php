<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tribunal;
use App\Models\Department;
use App\Models\CourtCase;
use App\Models\User;
use App\Models\CaseSession;
use App\Models\Participant;
use App\Models\RequestSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\CaseJudgment;
use App\Models\CourtSessionReport;


class TypistController extends Controller
{
    
    
   public function index(Request $request)
{
    // استدعاء المنطق الأساسي من showTypistCases
    $response = $this->showTypistCases();

    // جلب المستخدم
    $user = auth()->user();
    $tribunal   = Tribunal::find($user->tribunal_id);
    $department = Department::find($user->department_id);

    // إذا في case_id
    $case = null;
    if ($request->has('case_id')) {
        $case = CourtCase::with([
            'tribunal',
            'department',
            'sessions.judge'
        ])->find($request->case_id);
    }

    // دمج البيانات الإضافية مع الـ view اللي رجعته showTypistCases
    return view('clerk_dashboard.typist', array_merge(
        $response->getData(), // القضاة والقضايا
        [
            'tribunalName'    => $tribunal->name ?? '---',
            'tribunalNumber'  => $tribunal->number ?? '---',
            'departmentName'  => $department->name ?? '---',
            'departmentNumber'=> $department->number ?? '---',
            'userName'        => $user->full_name,
            'case'            => $case,
        ]
    ));
}

/**
 * ✅ تعرض نافذة جدول الدعوى للطابعة.
 * تجمع بيانات المحكمة، القلم، والجلسات المرتبطة بالدعوى المحددة.
 * تُستخدم لعرض جدول الجلسات بشكل منسق داخل واجهة الطابعة.
 */
public function showCaseSchedule($caseNumber)
{
    try {

        // 1️⃣ نجلب الدعوى عن طريق رقم الدعوى الحقيقي (number)
        $case = CourtCase::with([
            'tribunal:id,number',
            'department:id,number',
            'sessions.judge:id,full_name'
        ])
        ->where('number', $caseNumber)
        ->first();

        // 2️⃣ إذا رقم الدعوى غير موجود → نسجل بالـ Log التفاصيل
        if (!$case) {

            Log::warning('❌ رقم الدعوى غير موجود في DB', [
                'دخل المستخدم' => $caseNumber,
                'الأرقام_الموجودة_في_DB' => CourtCase::pluck('number')
            ]);

            return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
        }

        // 3️⃣ استخراج الجلسات المرتبطة
        $sessions = $case->sessions->map(function ($s) {
            return [
                'session_date'  => $s->session_date?->format('Y-m-d'),
                'session_time'  => $s->session_date?->format('H:i'),
                'judgment_type' => $s->judgment_type,
                'session_type'  => $s->session_type,
                'status'        => $s->status,
                'judge_name'    => $s->judge->full_name ?? '---',
            ];
        });

        // 4️⃣ نرجّع البيانات للواجهة
        return response()->json([
            'tribunal_number'   => $case->tribunal->number ?? '---',
            'department_number' => $case->department->number ?? '---',
            'sessions'          => $sessions
        ]);

    } catch (\Exception $e) {

        Log::error('❌ خطأ أثناء تحميل جدول الدعوى', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json(['error' => 'تعذر تحميل الجلسات'], 500);
    }
}
//نافذه أحكام الدعوى
//  دالة حفظ الحكم داخل TypistController
// تُسجل الحكم في جدول case_judgments بناءً على رقم الدعوى المدخل من المستخدم

public function loadCase($caseNumber)
{
    try {
        Log::info('🔍 بدء تحميل الدعوى برقم:', ['number' => $caseNumber]);

        $case = CourtCase::with(['tribunal', 'department'])
            ->where('number', $caseNumber)
            ->first();

        if (!$case) {
            Log::warning('⚠️ الدعوى غير موجودة برقم:', ['number' => $caseNumber]);
            return response()->json(['error' => 'الدعوى غير موجودة'], 404);
        }

        Log::info('✅ تم العثور على الدعوى:', ['id' => $case->id, 'year' => $case->year]);

        // 🔥 الآن نجلب الأطراف مع التهمة
        $participants = Participant::where('court_case_id', $case->id)
            ->select('id', 'name', 'type', 'charge') // 🔥 أضفنا "charge"
            ->get();

        Log::info('👥 عدد الأطراف المسترجعة:', ['count' => $participants->count()]);

        return response()->json([
            'case' => $case,
            'participants' => $participants
        ]);
    } catch (\Exception $e) {
        Log::error('❌ خطأ في loadCase:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'حدث خطأ أثناء تحميل الدعوى'], 500);
    }
}

public function saveJudgmentData(Request $request)
{
    try {
        Log::info('📝 بدء حفظ أو تحديث الحكم، البيانات المستلمة:', $request->all());

        $request->validate([
            'court_case_id'       => 'required|exists:court_cases,id',
            'participant_id'      => 'nullable|exists:participants,id',
            'judgment_id'         => 'nullable|exists:case_judgments,id',
            'judgment_mode'       => 'nullable|string',
            'judgment_date'       => 'nullable|date',
            'closure_date'        => 'nullable|date',
            'judgment_type'       => 'nullable|string',
            'charge_decision'     => 'nullable|string',
            'termination_type'    => 'nullable|string',
            'execution_details'   => 'nullable|string',
            'judgment_summary'    => 'nullable|string',
            'charge_text'         => 'nullable|string',
            'charge_split_type'   => 'nullable|string',
            'personal_drop_text'  => 'nullable|string',   // ← أضفناه هنا
        ]);

        // تحديث حكم موجود
        if ($request->filled('judgment_id')) {

            $judgment = CaseJudgment::find($request->judgment_id);

            $judgment->fill($request->only([
                'judgment_mode',
                'judgment_date',
                'closure_date',
                'judgment_type',
                'charge_decision',
                'termination_type',
                'execution_details',
                'judgment_summary',
                'charge_text',
                'charge_split_type',
            ]));

            // 🔥 إضافة النص الجديد
            $judgment->personal_drop_text = $request->personal_drop_text;

            $judgment->save();

            return response()->json(['message' => 'تم تحديث الحكم بنجاح', 'judgment' => $judgment]);
        }

        // إنشاء حكم جديد
        $judgment = CaseJudgment::create([
            'court_case_id'      => $request->court_case_id,
            'participant_id'     => $request->participant_id,
            'judgment_mode'      => $request->judgment_mode,
            'judgment_date'      => $request->judgment_date,
            'closure_date'       => $request->closure_date,
            'judgment_type'      => $request->judgment_type,
            'charge_decision'    => $request->charge_decision,
            'termination_type'   => $request->termination_type,
            'execution_details'  => $request->execution_details,
            'judgment_summary'   => $request->judgment_summary,
            'charge_text'        => $request->charge_text,
            'charge_split_type'  => $request->charge_split_type,
            'personal_drop_text' => $request->personal_drop_text,  // ← أضفناه هنا
            'created_by'         => auth()->id(),
        ]);

        return response()->json(['message' => 'تم حفظ الحكم الجديد بنجاح', 'judgment' => $judgment]);

    } catch (\Exception $e) {
        Log::error('❌ خطأ في saveJudgmentData:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'حدث خطأ أثناء حفظ أو تحديث الحكم'], 500);
    }
}





 // نافذه جدول أعمال القاضي
/**
 * ✅ جلب القضاة من جدول users حسب الدور
 */
public function getJudges()
{
    try {
        $judges = User::where('role', 'Judge')
                      ->select('id', 'full_name')
                      ->get();

        return response()->json($judges);
    } catch (\Exception $e) {
        Log::error('خطأ في جلب القضاة: ' . $e->getMessage());
        return response()->json(['error' => 'تعذر تحميل القضاة.'], 500);
    }
}

/**
 * ✅ جلب حالات الجلسات الفعلية من جدول case_sessions
 */
public function getSessionStatuses()
{
    try {
        $statuses = CaseSession::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->pluck('status');

        return response()->json($statuses);
    } catch (\Exception $e) {
        Log::error('خطأ في جلب حالات الجلسات: ' . $e->getMessage());
        return response()->json(['error' => 'تعذر تحميل الحالات.'], 500);
    }
}
/**
 * ✅ جلب جدول أعمال القاضي حسب الفلاتر
 */
public function getJudgeSchedule(Request $request)
{
    try {

        $query = CaseSession::query();

        // 🔹 فلترة حسب القاضي
        if ($request->filled('judge_id')) {
            $query->where('judge_id', $request->judge_id);
        }

        // 🔹 فلترة حسب حالة الجلسة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔹 تحميل العلاقات (الدعوى + المحكمة)
        $query->with(['courtCase.tribunal']);

        $sessions = $query->get()->map(function ($session) {

            return [

                // 🔸 رقم الدعوى (من جدول court_cases)
                'case_number'   => optional($session->courtCase)->number,

                // 🔸 تاريخ الجلسة + الوقت (من session_date)
                'date'          => $session->session_date->format('Y-m-d'),
                'time'          => $session->session_date->format('H:i'),

                // 🔸 المحكمة
                'tribunal_name' => optional(optional($session->courtCase)->tribunal)->name,

                // 🔸 نوع الجلسة
                'session_type'  => $session->session_type,

                // 🔸 حالة الجلسة
                'status'        => $session->status,

                // 🔸 السبب
                'reason'        => $session->postponed_reason,

                // 🔸 التاريخ الأصلي (من created_at في case_sessions)
                'original_date' => $session->created_at?->format('Y-m-d'),
            ];
        });

        return response()->json($sessions);

    } catch (\Exception $e) {

        Log::error('خطأ في جلب جدول أعمال القاضي: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json(['error' => 'حدث خطأ أثناء تحميل البيانات.'], 500);
    }
}





//جدول أعمال المحكمة
public function getCourtSchedule(Request $request)
{
    try {

        $query = CaseSession::query();

        // 🔹 فلترة حسب التاريخ
        if ($request->filled('date')) {
            $query->whereDate('session_date', $request->date);
        }

        // 🔹 فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔹 تحميل (المحكمة + الدعوى + القاضي)
        $query->with(['courtCase', 'courtCase.tribunal', 'judge']);

        $sessions = $query->get()->map(function ($session) {

            return [
                // 🔸 رقم الدعوى — من جدول court_cases
                'case_number'   => optional($session->courtCase)->number,

                'date'          => $session->session_date->format('Y-m-d'),
                'time'          => $session->session_date->format('H:i'),
                'session_type'  => $session->session_type,
                'status'        => $session->status,

                // المحكمة
                'tribunal_name' => optional(optional($session->courtCase)->tribunal)->name,

                // اسم القاضي من جدول users (full_name)
                'judge_name'    => optional($session->judge)->full_name,
            ];
        });

        return response()->json($sessions);

    } catch (\Exception $e) {

        Log::error("[getCourtSchedule] خطأ أثناء جلب جدول أعمال المحكمة", [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json(['error' => 'حدث خطأ أثناء تحميل البيانات.'], 500);
    }
}
// جدول أعمال المحكمة
public function getSessionStatusesForCourt()
{
    try {
        $statuses = CaseSession::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->pluck('status');

        return response()->json($statuses);

    } catch (\Exception $e) {

        // تسجيل الخطأ في اللوج
        Log::error("[getSessionStatusesForCourt] خطأ أثناء جلب الحالات", [
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
            'file'  => $e->getFile(),
        ]);

        return response()->json(['error' => 'تعذر تحميل حالات الجلسات.'], 500);
    }
}




// نافذة تحديد جلسات الدعوى
// نافذة تحديد جلسات الدعوى
public function showCaseDetails($caseNumber)
{
    $case = CourtCase::where('number', $caseNumber)
        ->with([
            'judge:id,full_name',
            'tribunal:id,number',
            'department:id,number',
            'participants', // ⚠️ مهم: بدون select حتى لا يُحذف court_case_id و id
            'sessions'
        ])
        ->first();

    if (!$case) {
        return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
    }

    return response()->json([
        'id'                => $case->id,
        'tribunal_number'   => optional($case->tribunal)->number,   // ✅ رقم المحكمة
        'department_number' => optional($case->department)->number, // ✅ رقم القلم
        'year'              => $case->court_year ?? optional($case->created_at)->format('Y'), // ✅ السنة
        'case_number'       => $case->number,
        'case_type'         => $case->type,
        'judge_name'        => optional($case->judge)->full_name,
        'judge_id'          => $case->judge_id,
        'participants'      => $case->participants,
        'created_at'        => optional($case->created_at)->format('Y-m-d'),
        'has_session'       => $case->sessions()->exists(),
    ]);
}
// نافذة تحديد جلسات الدعوى
public function setSession(Request $request)
{
    Log::info('🚀 دخلنا دالة setSession - Payload:', $request->all());

    try {

        // ⭐ قبل الفاليديشن اطبعي القيم المهمة
        Log::info('🔍 Checking IDs:', [
            'court_case_id' => $request->court_case_id,
            'judge_id' => $request->judge_id,
        ]);

        // ⭐ لو واحد منهم null اطبعي الخطأ مباشرة
        if (!$request->court_case_id || !$request->judge_id) {
            Log::error('❌ القيم غير مستلمة من الواجهة', [
                'court_case_id' => $request->court_case_id,
                'judge_id' => $request->judge_id
            ]);

            return response()->json([
                'message' => 'لم يتم استلام معرف القضية أو القاضي',
                'debug' => $request->all()
            ], 422);
        }

        // ⭐ الفاليديشن
        $request->validate([
            'court_case_id' => 'required|integer|exists:court_cases,id',
            'judge_id' => 'required|integer|exists:users,id',
            'session_date' => 'required|date_format:Y-m-d H:i:s',
            'session_time' => 'required|string',
            'session_goal' => 'required|string|max:255',
            'judgment_type' => 'required|string',
            'status' => 'required|string',
        ]);

        // ⭐ حفظ الجلسة
        CaseSession::create([
            'court_case_id'  => $request->court_case_id,
            'judge_id'       => $request->judge_id,
            'session_date'   => $request->session_date,
            'session_time'   => $request->session_time,
            'session_goal'   => $request->session_goal,
            'judgment_type'  => $request->judgment_type,
            'status'         => $request->status,
            'created_by'     => auth()->id(),
        ]);

        Log::info('✅ تم حفظ الجلسة بنجاح');

        return response()->json([
            'message' => 'تم حفظ الجلسة بنجاح'
        ]);

    } catch (\Exception $e) {

        Log::error('❌ فشل حفظ الجلسة:', [
            'error' => $e->getMessage(),
            'input' => $request->all()
        ]);

        return response()->json([
            'message' => 'تعذر حفظ الجلسة',
            'error' => $e->getMessage()
        ], 500);
    }
}
// نافذة تحديد جلسات الدعوى
public function hasSession($caseNumber)
{
    $case = CourtCase::where('number', $caseNumber)->first();

    if (!$case) {
        return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
    }

    $hasSession = $case->sessions()->exists();

    return response()->json(['has_session' => $hasSession]);
}



















// نافذة إعادة تحديد الجلسات
public function getSession($caseNumber)
{
    $case = CourtCase::where('number', $caseNumber)
        ->with([
            'tribunal:id,number',
            'department:id,number',
        ])
        ->first();

    if (!$case) {
        return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
    }

    $session = $case->sessions()->latest('session_date')->first();

    if (!$session) {
        return response()->json(['error' => 'لا توجد جلسة محددة'], 404);
    }

    return response()->json([
        'id'                => $session->id,
        'session_date'      => optional($session->session_date)->format('Y-m-d'),
        'session_time'      => optional($session->session_date)->format('H:i'),
        'session_goal'      => $session->session_goal,
        // ✅ إضافات جديدة
        'tribunal_number'   => optional($case->tribunal)->number,
        'department_number' => optional($case->department)->number,
        'year'              => $case->court_year ?? optional($case->created_at)->format('Y'),
        'case_number'       => $case->number,
    ]);
}

// نافذة إعادة تحديد الجلسات
public function deleteCaseSession($sessionId)
{
    $session = CaseSession::find($sessionId);

    if (!$session) {
        return response()->json(['error' => 'الجلسة غير موجودة'], 404);
    }

    $session->delete();

    return response()->json(['message' => 'تم حذف الجلسة بنجاح']);
}



//نافذة إلغاء جلسات الدعوى
//نافذة إلغاء جلسات الدعوى
public function getCancelCaseDetails($caseNumber)
{
    $case = CourtCase::where('number', $caseNumber)
        ->with([
            'judge:id,full_name',
            'tribunal:id,number',
            'department:id,number',
            'participants' => function ($query) {
                $query->select('court_case_id', 'type', 'name');
            }
        ])
        ->first();

    if (!$case) {
        return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
    }

    return response()->json([
        'id' => $case->id,
        'tribunal_number' => optional($case->tribunal)->number,
        'department_number' => optional($case->department)->number,
        'year' => optional($case->created_at)->format('Y'),
        'case_number' => $case->number,
        'case_type' => $case->type,
        'judge_name' => optional($case->judge)->full_name,
        'participants' => $case->participants ?? [],
        'created_at' => optional($case->created_at)->format('Y-m-d'),
    ]);
}
//نافذة إلغاء جلسات الدعوى
public function getCancelSession($caseNumber)
{
    $case = CourtCase::where('number', $caseNumber)->first();

    if (!$case) {
        return response()->json(['error' => 'رقم الدعوى غير موجود'], 404);
    }

    $session = $case->sessions()->latest('session_date')->first();

    if (!$session) {
        return response()->json(['error' => 'لا توجد جلسة محددة'], 404);
    }

    return response()->json([
        'id' => $session->id,
        'session_date' => optional($session->session_date)->format('Y-m-d'),
        'session_time' => optional($session->session_date)->format('H:i'),
        'session_goal' => $session->session_goal,
    ]);
}
//نافذة إلغاء جلسات الدعوى
public function deleteCancelSession($sessionId)
{
    $session = CaseSession::find($sessionId);

    if (!$session) {
        return response()->json(['error' => 'الجلسة غير موجودة'], 404);
    }

    $session->delete();

    return response()->json(['message' => '✅ تم إلغاء الجلسة']);
}




















































//جدول الطلبات
public function showRequestSchedule(Request $request)
{
    $requestNumber = $request->input('request_number');

    // جلب الجلسات المرتبطة برقم الطلب مع العلاقات المطلوبة
    $schedules = RequestSchedule::with(['judge', 'tribunal', 'department'])
        ->where('request_number', $requestNumber)
        ->get();

    // تجهيز البيانات للواجهة
    $formatted = $schedules->map(function ($schedule) {
        return [
            'session_date'       => $schedule->session_date,
            'session_time'       => $schedule->session_time,
            'session_status'     => $schedule->session_status,
            'session_reason'     => $schedule->session_reason,
            'original_date'      => $schedule->original_date,
            'judge_name'         => optional($schedule->judge)->full_name,

            // ✅ الحقول الإضافية المطلوبة للعرض في أعلى النافذة
            'tribunal_number'    => optional($schedule->tribunal)->number,
            'department_number'  => optional($schedule->department)->number,
            'court_year'         => $schedule->court_year,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formatted
    ]);
}





//تحديد جلسات الطلب
public function showRequestDetails($requestNumber)
{
    try {
        $schedule = RequestSchedule::with(['tribunal', 'department', 'judge'])
            ->where('request_number', $requestNumber)
            ->first();

        if (!$schedule) {
            Log::error("طلب غير موجود: رقم الطلب = {$requestNumber}");
            return response()->json(['error' => 'الطلب غير موجود'], 404);
        }

        $details = [
            'id'                => $schedule->id,
            'request_number'    => $schedule->request_number,

            // المحكمة
            'tribunal_number'   => optional($schedule->tribunal)->number,
            'department_number' => optional($schedule->department)->number,
            'court_year'        => $schedule->court_year,

            // الطلب
            'title'             => $schedule->title,

            // الأطراف
            'plaintiff'         => $schedule->plaintiff_name,
            'defendant'         => $schedule->defendant_name,
            'third_party'       => $schedule->third_party_name,

            // ⭐ عرض تاريخ إدخال الطلب (created_at)
            'original_date'     => $schedule->created_at 
                                   ? $schedule->created_at->format('Y-m-d') 
                                   : '',

            // القاضي
            'judge_name'        => optional($schedule->judge)->full_name,

            // الجلسة
            'session_date'      => $schedule->session_date,
            'session_time'      => $schedule->session_time,
            'session_reason'    => $schedule->session_reason,

            // ⭐ إضافة حالة الجلسة
            'session_status'    => $schedule->session_status,
        ];

        return response()->json($details);

    } catch (\Exception $e) {
        Log::error("خطأ في showRequestDetails: " . $e->getMessage());
        return response()->json(['error' => 'حدث خطأ أثناء تحميل تفاصيل الطلب'], 500);
    }
}

//تحديد جلسات الطلب
public function storeSession(Request $request)
{
    try {
        $schedule = RequestSchedule::find($request->input('id'));

        if (!$schedule) {
            Log::error("طلب غير موجود عند التخزين: ID = " . $request->input('id'));
            return back()->with('error', 'الطلب غير موجود');
        }

        if ($schedule->session_date || $schedule->session_time) {
            Log::warning("محاولة تكرار جلسة: ID = {$schedule->id}");
            return back()->with('error', 'تم تحديد جلسة مسبقًا لهذا الطلب');
        }

        $schedule->session_date   = $request->input('session_date');
        $schedule->session_time   = $request->input('session_time');
        $schedule->session_reason = $request->input('session_reason');

        // ⭐ تخزين حالة الجلسة
        $schedule->session_status = $request->input('session_status');

        $schedule->save();

        Log::info("تم تحديد جلسة للطلب: ID = {$schedule->id}");

        if ($request->has('finish')) {
            return redirect()->route('typist.dashboard')->with('success', 'تم تحديد الجلسة وإنهاء الطلب');
        }

        return back()->with('success', 'تم تحديد الجلسة بنجاح');
    } catch (\Exception $e) {
        Log::error("خطأ في storeSession: " . $e->getMessage());
        return back()->with('error', 'حدث خطأ أثناء حفظ الجلسة');
    }
}









//نافذه إعادة تحديد جلسات الطلبات
// نافذة إعادة تحديد جلسات الطلبات
public function rescheduleDetails($requestNumber)
{
    $request = \App\Models\RequestSchedule::with(['tribunal', 'department', 'judge'])
        ->where('request_number', $requestNumber)
        ->first();

    if (!$request) {
        return response()->json(['error' => 'الطلب غير موجود'], 404);
    }

    return response()->json([
        'id'                => $request->id,
        'tribunal_number'   => optional($request->tribunal)->number,
        'department_number' => optional($request->department)->number,
        'court_year'        => $request->court_year,
        'request_number'    => $request->request_number,
        'title'             => $request->title,

        // ✔ الأطراف
        'plaintiff'         => $request->plaintiff_name,
        'defendant'         => $request->defendant_name,
        'third_party'       => $request->third_party_name,

        // ✔ التاريخ الأصلي = created_at
        'original_date'     => optional($request->created_at)->format('Y-m-d'),

        'judge_name'        => optional($request->judge)->full_name,

        // ✔ بيانات الجلسة
        'session_date'      => $request->session_date,
        'session_time'      => $request->session_time,
        'session_reason'    => $request->session_reason,

        // ⭐ إضافة حالة الجلسة
        'session_status'    => $request->session_status,
    ]);
}

// نافذة إعادة تحديد جلسات الطلبات
public function deleteSession(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:request_schedules,id',
    ]);

    $record = \App\Models\RequestSchedule::find($validated['id']);
    $record->session_date   = null;
    $record->session_time   = null;
    $record->session_reason = null;
    $record->session_status = null; // ⭐ مسح الحالة أيضًا
    $record->save();

    return response()->json(['success' => 'تم حذف موعد الجلسة القديم بنجاح']);
}

// نافذة إعادة تحديد جلسات الطلبات
public function rescheduleSession(Request $request)
{
    $validated = $request->validate([
        'id'            => 'required|exists:request_schedules,id',
        'session_date'  => 'required|date',
        'session_time'  => 'required',
        'session_reason'=> 'required|string',
        'session_status'=> 'required|string', // ⭐ التحقق من الحالة
    ]);

    $record = \App\Models\RequestSchedule::find($validated['id']);
    $record->session_date   = $validated['session_date'];
    $record->session_time   = $validated['session_time'];
    $record->session_reason = $validated['session_reason'];
    $record->session_status = $validated['session_status']; // ⭐ تخزين الحالة الجديدة
    $record->save();

    return response()->json(['success' => 'تم حفظ موعد الجلسة الجديد بنجاح']);
}











//نافذه إلغاء جلسات الطلبات
// نافذه إلغاء جلسات الطلبات
public function cancelDetails($requestNumber)
{
    $request = \App\Models\RequestSchedule::with(['tribunal', 'department', 'judge'])
        ->where('request_number', $requestNumber)
        ->first();

    if (!$request) {
        return response()->json(['error' => 'الطلب غير موجود'], 404);
    }

    return response()->json([
        'id'                => $request->id,
        'tribunal_number'   => optional($request->tribunal)->number,
        'department_number' => optional($request->department)->number,
        'court_year'        => $request->court_year,
        'request_number'    => $request->request_number,
        'title'             => $request->title,

        // ✅ الأطراف من *_name
        'plaintiff'         => $request->plaintiff_name,
        'defendant'         => $request->defendant_name,
        'third_party'       => $request->third_party_name,

        // ✅ التاريخ الأصلي = تاريخ إدخال الطلب للنظام
        'original_date'     => optional($request->created_at)->format('Y-m-d'),

        // ✅ اسم القاضي من full_name
        'judge_name'        => optional($request->judge)->full_name,

        'session_date'      => $request->session_date,
        'session_time'      => $request->session_time,
        'session_reason'    => $request->session_reason,
    ]);
}
//نافذه إلغاء جلسات الطلبات
public function cancelSession(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:request_schedules,id',
    ]);

    $record = \App\Models\RequestSchedule::find($validated['id']);
    $record->session_date = null;
    $record->session_time = null;
    $record->session_reason = null;
    $record->save();

    return response()->json(['success' => 'تم إلغاء الجلسة بنجاح']);
}


















public function showTypistCases()
{
    $typist = auth()->user(); // المستخدم (الطابعة)

    // 🔵 1) جلب القضاة المفروض تتابعهم الطابعة
    $assignedJudges = \App\Models\JudgeUser::where('user_id', $typist->id)
                      ->pluck('judge_id')
                      ->toArray();

    // إذا ما في قضاة مرتبطين بهاي الطابعة
    if (empty($assignedJudges)) {
        return view('clerk_dashboard.typist', [
            'cases' => [],
            'judgeNames' => [],
        ]);
    }

    // 🔵 2) جلب أسماء القضاة للعرض في الصفحة
    $judgeNames = User::whereIn('id', $assignedJudges)
                    ->pluck('full_name')
                    ->toArray();

    // 🔵 3) جلب القضايا الخاصة بالقضاة المحددين
    $cases = \App\Models\CourtCase::whereIn('judge_id', $assignedJudges)
                ->with('judge') // جلب اسم القاضي للقضية
                ->orderBy('created_at', 'desc')
                ->get();

    return view('clerk_dashboard.typist', [
        'cases'      => $cases,
        'judgeNames' => $judgeNames,
    ]);
}







//محضر المحاكمة

public function showTrialReport(CaseSession $session)
{
    $case   = $session->courtCase;
    $judge  = $case->judge;
    $typist = auth()->user();

    // ⭐⭐ أهم نقطة: تحميل كل المحاضر المخزنة سابقاً لهذا الـ SESSION
    $reports = CourtSessionReport::where('case_session_id', $session->id)->get();

    // ⭐ أقوال الأطراف الأساسية
    $participants = $case->participants;

    // ⭐ الأطراف المضافة (participant_id = null)
    $added_parties = $reports
        ->where('participant_id', null)
        ->where('name', '!=', null);

    // ⭐ القرار القديم المحفوظ
    $savedDecision = $reports
        ->where('decision_text', '!=', null)
        ->first();

    return view('clerk_dashboard.trial_report', compact(
        'session',
        'case',
        'judge',
        'typist',
        'participants',
        'reports',          // ⬅ مهم جداً
        'added_parties',
        'savedDecision'
    ));
}
public function storeTrialReport(Request $request, CaseSession $session)
{
    $case = $session->courtCase;

    // ⭐ نوع المحضر (trial / after)
    $mode = $request->report_mode ?? 'trial';

    // =============================
    // 1) حفظ أقوال الأطراف الأساسيين
    // =============================
    if ($request->participants) {
        foreach ($request->participants as $pid => $data) {

            $p = Participant::find($pid);

            CourtSessionReport::create([
                'case_session_id' => $session->id,
                'court_case_id'   => $case->id,
                'participant_id'  => $pid,
                'name'            => $p->name,
                'role'            => $p->type,
                'statement_text'  => $data['statement'] ?? null,
                'fingerprint'     => $data['fingerprint'] ?? null,
                'report_mode'     => $mode,   // 🔵 أهم سطر
            ]);
        }
    }

    // =============================
    // 2) حفظ الأطراف المضافة
    // =============================
    if ($request->new_parties) {
        foreach ($request->new_parties as $part) {

            CourtSessionReport::create([
                'case_session_id' => $session->id,
                'court_case_id'   => $case->id,
                'participant_id'  => null,
                'name'            => $part['name'],
                'role'            => $part['role'],
                'statement_text'  => $part['statement'] ?? null,
                'fingerprint'     => $part['fingerprint'] ?? null,
                'report_mode'     => $mode,   // 🔵 مهم جداً
            ]);
        }
    }

    // =============================
    // 3) حفظ القرار النهائي
    // =============================
    CourtSessionReport::create([
        'case_session_id' => $session->id,
        'court_case_id'   => $case->id,
        'participant_id'  => null,
        'name'            => null,
        'statement_text'  => null,
        'fingerprint'     => $request->judge_fingerprint,
        'decision_text'   => $request->decision_text,
        'report_mode'     => $mode,   // 🔵 لازم ينحفظ هون أيضاً
    ]);

    return redirect()->back()->with('success', 'تم حفظ المحضر بنجاح');
}






















public function showAfterTrialReport(CaseSession $session)
{
    $case = $session->courtCase;
    $judge = $case->judge;
    $typist = auth()->user();

    // الأطراف الأساسيين
    $participants = $case->participants;

    // ⭐ تحميل كل محاضر ما بعد
    $reports = CourtSessionReport::where('case_session_id', $session->id)
                                 ->where('report_mode', 'after')
                                 ->get();

    // ⭐ الأطراف المضافة سابقاً
    $added_parties = $reports->where('participant_id', null)
                             ->where('name', '!=', null);

    // ⭐ القرار المحفوظ
    $savedDecision = $reports->where('decision_text', '!=', null)->first();

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
public function storeAfterTrialReport(Request $request, CaseSession $session)
{
    $case = $session->courtCase;
    $mode = "after"; // ⭐ نوع المحضر

    // =============================
    // 1) حفظ أقوال الأطراف الأساسيين
    // =============================
    if ($request->participants) {
        foreach ($request->participants as $pid => $data) {

            $p = Participant::find($pid);

            CourtSessionReport::updateOrCreate(
                [
                    'case_session_id' => $session->id,
                    'participant_id'  => $pid,
                    'report_mode'     => $mode,   // ⭐ مهم
                ],
                [
                    'court_case_id'   => $case->id,
                    'name'            => $p->name,
                    'role'            => $p->type,
                    'statement_text'  => $data['statement'] ?? null,
                ]
            );
        }
    }

    // =============================
    // 2) حفظ الأطراف الجديدة
    // =============================
    if ($request->new_parties) {
        foreach ($request->new_parties as $part) {

            CourtSessionReport::create([
                'case_session_id' => $session->id,
                'court_case_id'   => $case->id,
                'participant_id'  => null,
                'name'            => $part['name'],
                'role'            => $part['role'],
                'statement_text'  => $part['statement'] ?? '',
                'report_mode'     => $mode, // ⭐ مهم
            ]);
        }
    }

    // =============================
    // 3) حفظ القرار
    // =============================
    CourtSessionReport::updateOrCreate(
        [
            'case_session_id' => $session->id,
            'participant_id'  => null,
            'report_mode'     => $mode,
        ],
        [
            'court_case_id'   => $case->id,
            'decision_text'   => $request->decision_text,
        ]
    );

    return redirect()->back()->with('success', 'تم حفظ محضر ما بعد بنجاح');
}











//نافذه أحكام الطلب
public function openJudgmentModal(Request $request)
{
    $reqNumber = $request->input('request_number');

    $requestSchedule = RequestSchedule::where('request_number', $reqNumber)
                                     ->with(['tribunal', 'department'])
                                     ->first();

    if (!$requestSchedule) {
        return response()->json(['error' => 'NOT_FOUND'], 404);
    }

    // إرجاع الأطراف الجديدة
    $parties = [
        'plaintiff'    => $requestSchedule->plaintiff_name,
        'defendant'    => $requestSchedule->defendant_name,
        'third_party'  => $requestSchedule->third_party_name,
        'lawyer'       => $requestSchedule->lawyer_name,
    ];

    return response()->json([
        'request' => $requestSchedule,
        'parties' => $parties,
    ]);
}
public function storeJudgment(Request $request)
{
    try {

        // استقبال البيانات والتحقق منها
        $validated = $request->validate([
            'request_id'     => 'required|integer|exists:request_schedules,id',
            'judgment_date'  => 'required|date',
            'closure_date'   => 'required|date',

            'text_against'   => 'nullable',
            'text_final'     => 'nullable|string',
            'text_waiver'    => 'nullable|string',
        ]);

        // جلب السجل
        $req = RequestSchedule::findOrFail($validated['request_id']);

        // ============================
        // حفظ تاريخ الحكم والإغلاق
        // ============================
        $req->judgment_date = $validated['judgment_date'];
        $req->closure_date  = $validated['closure_date'];

        // ============================
        // الحكم ضد الأطراف
        // ============================
        $against = $validated['text_against'];

        // إذا البيانات جايه string → نحولها JSON
        if (is_string($against)) {
            $against = json_decode($against, true);
        }

        if (is_array($against)) {

            if (isset($against['plaintiff'])) {
                $req->judgment_text_plaintiff = $against['plaintiff'];
            }

            if (isset($against['defendant'])) {
                $req->judgment_text_defendant = $against['defendant'];
            }

            if (isset($against['third_party'])) {
                $req->judgment_text_third_party = $against['third_party'];
            }

            if (isset($against['lawyer'])) {
                $req->judgment_text_lawyer = $against['lawyer'];
            }
        }

        // ============================
        // الحكم الفاصل
        // ============================
        if (!empty($validated['text_final'])) {
            $req->judgment_text_final = $validated['text_final'];
        }

        // ============================
        // إسقاط الحق الشخصي
        // ============================
        if (!empty($validated['text_waiver'])) {
            $req->judgment_text_waiver = $validated['text_waiver'];
        }

        // تحديث حالة الطلب حسب النظام
        $req->session_purpose = 'حكم صادر';
        $req->session_status  = 'مغلق';

        $req->save();

        return response()->json([
            'message' => '✔ تم حفظ جميع بيانات الحكم بنجاح',
            'request' => $req
        ]);

    } catch (\Exception $e) {

        Log::error("❌ خطأ storeJudgment", [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'error' => 'حدث خطأ أثناء حفظ بيانات الحكم'
        ], 500);
    }
}
}