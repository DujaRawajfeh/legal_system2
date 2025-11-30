<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WriterController;
use App\Http\Controllers\TypistController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\ArchiverController;
use App\Http\Controllers\ChiefController;


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']); 
Route::get('/', function () {return redirect()->route('login');});




Route::get('/writer/dashboard', [WriterController::class, 'dashboard'])->middleware('auth')->name('writer.dashboard');
//تسجيل دعوى
Route::post('/court-cases/store', [WriterController::class, 'storeCourtCase'])->name('courtCases.store');
Route::post('/participants/store', [WriterController::class, 'storeParticipant']);
Route::get('/writer/get-next-available-judge', [WriterController::class, 'getNextAvailableJudge']);
Route::get('/cases/{id}', [WriterController::class, 'show'])->name('cases.show');


//مذكرة تبليغ
Route::get('/court-cases/{number}', [WriterController::class, 'fetchCaseDetails']);
Route::post('/notifications/save', [WriterController::class, 'saveNotification'])->name('notifications.save');



Route::post('/cases/pull', [WriterController::class, 'pullFromModal'])->name('cases.pull');


Route::post('/writer/pull-police-case/{id}', [WriterController::class, 'pullFromPoliceCase'])->name('writer.pull-police-case');
Route::get('/writer/assign-judge/{departmentId}', [WriterController::class, 'assignJudge'])->name('writer.assign-judge');
Route::get('/police-cases/by-center/{center}', [WriterController::class, 'getPoliceCasesByCenter']);
//مذكرة توقيف
Route::post('/writer/arrest-memo', [WriterController::class, 'handleArrestMemo']);
Route::post('/writer/extend-arrest-memo', [WriterController::class, 'extendArrestMemo']);

Route::post('/civil-registry/search', [WriterController::class, 'searchCivilRegistry']);
//مذكرة الإفراج عن الموقوفين
Route::post('/release-memo/store', [WriterController::class, 'storeReleaseMemo'])->name('release.memo.store');
Route::get('/release-memo/fetch', [WriterController::class, 'fetchCaseParticipants']);
Route::get('/release-memo/default-info', [WriterController::class, 'defaultInfo']);






Route::get('/typist', [TypistController::class, 'index'])->name('typist.index');

Route::get('/typist/cases', [TypistController::class, 'showTypistCases'])->name('typist.cases');


Route::get('/typist/case-schedule/{caseNumber}', [TypistController::class, 'showCaseSchedule'])->name('case.schedule');
//نافذه أحكام الدعوى
Route::get('/judgment/{caseNumber}', [TypistController::class, 'loadCase']);
Route::post('/typist/judgment/save', [TypistController::class, 'saveJudgmentData'])->name('judgment.save');


Route::get('/judges', [TypistController::class, 'getJudges'])->name('judges');
Route::get('/session-statuses', [TypistController::class, 'getSessionStatuses'])->name('session.statuses');
Route::get('/judge-schedule', [TypistController::class, 'getJudgeSchedule'])->name('judge.schedule');

Route::get('/court-schedule', [TypistController::class, 'getCourtSchedule'])->name('court.schedule');
Route::get('/session-statuses-court', [TypistController::class, 'getSessionStatusesForCourt'])->name('session.statuses.court');

Route::post('/typist/request-schedule', [TypistController::class, 'showRequestSchedule'])->name('typist.requestSchedule');




Route::get('/typist/request/{id}/details', [TypistController::class, 'showRequestDetails'])->name('typist.request.details');
Route::post('/typist/request/store-session', [TypistController::class, 'storeSession'])->name('typist.request.store-session');

Route::get('/typist/reschedule/{requestNumber}/details', [TypistController::class, 'rescheduleDetails']);
Route::post('/typist/request/delete-session', [TypistController::class, 'deleteSession'])->name('typist.request.delete-session');
Route::post('/typist/request/reschedule-session', [TypistController::class, 'rescheduleSession'])->name('typist.request.reschedule-session');
//نافذه إلغاء جلسات الطلبات
Route::get('/typist/cancel/{requestNumber}/details', [TypistController::class, 'cancelDetails']);
Route::post('/typist/request/cancel-session', [TypistController::class, 'cancelSession'])->name('typist.request.cancel-session');



//نافذه تحديد جلسات الدعوى
Route::get('/typist/case-details/{caseNumber}', [TypistController::class, 'showCaseDetails']);
Route::post('/typist/set-session', [TypistController::class, 'setSession']);
Route::get('/typist/has-session/{caseNumber}', [TypistController::class, 'hasSession']);
//نافذه إعادة تحديد الجلسات
Route::get('/typist/get-session/{caseNumber}', [TypistController::class, 'getSession']);
Route::delete('/typist/delete-case-session/{sessionId}', [TypistController::class, 'deleteCaseSession']);

//نافذة إلغاء جلسات الدعوى
Route::get('/typist/cancel-case-details/{caseNumber}', [TypistController::class, 'getCancelCaseDetails']);
Route::get('/typist/cancel-session/{caseNumber}', [TypistController::class, 'getCancelSession']);
Route::delete('/typist/cancel-session-delete/{sessionId}', [TypistController::class, 'deleteCancelSession']);





Route::get('/typist/session/{session}/report', [TypistController::class, 'showTrialReport'])->name('trial.report');
Route::post('/typist/session/{session}/report/store', [TypistController::class, 'storeTrialReport'])->name('trial.report.store');

// صفحة ما بعد المحاكمة
Route::get('/after-trial-report/{session}', [TypistController::class, 'showAfterTrialReport'])->name('after.trial.report');
Route::post('/after-trial-report/{session}',[TypistController::class, 'storeAfterTrialReport'])->name('after.trial.report.store');
//إدارة تباليغ الدعوى
Route::get('/writer/case-notifications/{caseNumber}', [WriterController::class, 'getCaseNotifications'])->name('writer.case.notifications');
//نافذه المذكرات
Route::get('/writer/arrest-memos/{caseNumber}', [WriterController::class, 'getArrestMemos'])->name('writer.arrest.memos');







Route::get('/judge', [JudgeController::class, 'index'])->name('judge.index');


Route::get('/archiver', [ArchiverController::class, 'index'])->name('archiver.page');
Route::post('/archived-documents', [ArchiverController::class, 'store'])->name('archived-documents.store');


//نافذة تسجيل طلب
Route::post('/writer/request/store-number', [WriterController::class, 'storeRequest']);
Route::post('/requests/store-parties', [WriterController::class, 'storeRequestParties'])->name('requests.storeParties');
Route::get('/requests/get-next-judge', [WriterController::class, 'getNextJudgeForRequest'])->name('requests.nextJudge');

//نافذه أحكام الطلب
Route::get('/typist/judgment/open', [TypistController::class, 'openJudgmentModal'])->name('typist.judgment.open');
Route::post('/typist/judgment/store', [TypistController::class, 'storeJudgment'])->name('typist.judgment.store');







// لوحة رئيس القسم
Route::get('/chief/dashboard', [ChiefController::class, 'dashboard'])->name('chief.dashboard');
// جلب القضاة
Route::get('/chief/judges', [ChiefController::class, 'getJudges'])->name('chief.judges');
// تحويل الدعوى
Route::post('/chief/transfer-case', [ChiefController::class, 'transferCase'])->name('chief.transfer');


//نافذه تعين قاضي
Route::get('/chief/get-users-judges', [ChiefController::class, 'getUsersAndJudges'])->name('chief.getUsersJudges');
Route::post('/chief/assign-judge', [ChiefController::class, 'saveAssignment'])->name('chief.assignJudge');
Route::get('/chief/employees', [ChiefController::class, 'getEmployees'])->name('chief.employees');


Route::get('/chief/detained-list', [ChiefController::class, 'getDetainedList'])->name('chief.detainedList');



//عرض محاضر الجلسات
Route::get('/writer/reports/list', [WriterController::class, 'loadReportsList'])->name('writer.reports.list');
// ✅ فتح محضر المحاكمة للكاتب (نفس دالة الطابعة)
Route::get('/writer/trial-report/{session}', [TypistController::class, 'showTrialReport'])->name('writer.trial.report.show');
// ✅ فتح محضر ما بعد للكاتب (نفس دالة الطابعة)
Route::get('/writer/after-trial-report/{session}', [TypistController::class, 'showAfterTrialReport'])->name('writer.after-trial.report.show');


//هاض المحاضر صفحة القاضي
Route::get('/judge/trial-report/{session}', [JudgeController::class, 'showTrialReport'])->name('judge.trial.report');
Route::get('/judge/after-trial-report/{session}', [JudgeController::class, 'showAfterTrialReport'])->name('judge.after.report');


// طلبات اليوم للقاضي
Route::get('/judge/requests/today', [JudgeController::class, 'getTodayRequests'])->name('judge.requests.today');
//  كل طلبات القاضي (مع الأطراف والأحكام)
Route::get('/judge/requests/all', [JudgeController::class, 'getAllRequests'])->name('judge.requests.all');


//رقم الدعوى الشريط الثالث
Route::post('/chief/request-details', [ChiefController::class, 'loadRequestDetails'])
    ->name('chief.request.details');
//رقم الدعوى الشريط الثالث
Route::post('/chief/case-details', [ChiefController::class, 'loadCaseDetails'])
    ->name('chief.case.details');






Route::post('/writer/request-schedule', [WriterController::class, 'showRequestSchedule']);



Route::post('/update-password', [AuthController::class, 'updatePassword'])
    ->name('password.update')
    ->middleware('auth');




Route::get('/change-password', function () {
    return view('login');
})->name('password.change');



Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');







// راوتات إعداد وتفعيل/تعطيل 2FA (للمستخدم المسجل دخوله)
Route::middleware('auth')->group(function () {
    Route::get('/2fa/setup', [AuthController::class, 'show2FASetup'])->name('2fa.setup');
    Route::post('/2fa/enable', [AuthController::class, 'enable2FA'])->name('2fa.enable');
    Route::post('/2fa/disable', [AuthController::class, 'disable2FA'])->name('2fa.disable');
});

// راوتات التحقق من الرمز بعد كلمة السر
Route::get('/2fa/verify', [AuthController::class, 'show2FAVerify'])->name('2fa.verify.form');
Route::post('/2fa/verify', [AuthController::class, 'verify2FA'])->name('2fa.verify');









