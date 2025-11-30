
@extends('layouts.app')

@section('title', 'صفحة الطابعة')

@section('content')
{{-- ✅ قائمة الجلسات الخاصة بالطابعة (تظهر عند المرور على الكلمة الموجودة في layouts.app) --}}
<div id="sessions-menu-typist" class="position-absolute bg-white border rounded shadow-sm px-2 py-1"
     style="display: none; top: 38px; right: 12px; z-index: 1000; min-width: 220px;">
    <div class="dropdown-item" role="button" tabindex="0" onclick="openCourtScheduleModal()">
  جدول أعمال المحكمة
</div>
    <div class="dropdown-item" data-bs-toggle="modal" data-bs-target="#judgeScheduleModal">جدول أعمال القاضي</div>
    <div class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#caseScheduleModal">
    جدول الدعوى
</div>
    <div class="dropdown-item" data-bs-toggle="modal" data-bs-target="#setCaseSessionModal">
  تحديد جلسات الدعوى
</div>
   <div class="dropdown-item" data-bs-toggle="modal" data-bs-target="#rescheduleSessionModal">
    إعادة تحديد جلسات الدعوى
</div>
    <div class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cancelSessionModal"> إلغاء جلسات الدعوى</div>
    <div class="dropdown-item" data-bs-toggle="modal" data-bs-target="#judgmentModal">أحكام الدعوى</div>
  
</div>



<!-- قسم عرض القضايا المرتبطة بالقاضي -->
<style>
    .case-box {
        font-size: 14px;          /* تصغير الخط */
        line-height: 1.3;         /* تقليل المسافات بين السطور */
        padding: 12px !important;
    }

    .case-box h4 {
        font-size: 16px;          
        margin-bottom: 6px;
    }

    .case-box p {
        margin: 2px 0;            /* تصغير المسافات */
    }

    .case-box .btn {
        padding: 4px 10px;
        font-size: 13px;
        margin-right: 5px;
    }

    #judge-cases-section h2 {
        font-size: 18px;
        margin-bottom: 15px;
    }
</style>

<!-- قسم عرض القضايا المرتبطة بالقضاة -->
<div id="judge-cases-section" class="mt-3">

    {{-- عرض أسماء القضاة المرتبطين --}}
    @if(!empty($judgeNames))
        <h2>
                      القاضي:
            {{ implode(' ، ', $judgeNames) }}
        </h2>
    @else
        <p>لا يوجد قضاة مرتبطون بهذه الطابعة.</p>
    @endif


    <hr>

    {{-- عرض القضايا --}}
    @forelse($cases as $case)
        <div class="case-box mb-3 p-3 border rounded">

            {{-- رقم القضية --}}
            <h4>رقم الدعوى: {{ $case->number }}</h4>

            {{-- عنوان القضية --}}
            <p>عنوان الدعوى: {{ $case->type }}</p>

            @php 
                // جلب أول جلسة
                $session = $case->sessions->first(); 
            @endphp

            @if($session)

                {{-- تاريخ الجلسة --}}
                <p>تاريخ الجلسة: {{ $session->session_date }}</p>

                {{-- حالة الجلسة --}}
                <p>حالة الجلسة: {{ $session->status }}</p>

                {{-- الأزرار حسب الحالة --}}
                @if($session->status === 'محددة')
                    
                    <a href="{{ route('trial.report', $session->id) }}"
                       class="btn btn-primary">
                        محضر المحاكمة
                    </a>

                @elseif(in_array($session->status, ['مستمرة','مكتملة']))

                    <a href="{{ route('trial.report', $session->id) }}"
                       class="btn btn-primary">
                        محضر المحاكمة
                    </a>

                    <a href="{{ route('after.trial.report', $session->id) }}"
                       class="btn btn-secondary">
                        محضر المحاكمة/ما بعد
                    </a>

                @endif

            @else
                <p>لا توجد جلسة لهذه القضية.</p>
            @endif

        </div>
    @empty
        <p class="text-danger">لا يوجد قضايا مرتبطة بأي قاضي.</p>
    @endforelse

</div>


<!-- ✅ هذا الكود يظهر قائمة الجلسات فقط إذا كان النوع المختار هو "دعوى" -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.getElementById('sessions-trigger');
    const menu = document.getElementById('sessions-menu-typist');

    function getCurrentType() {
        const selected = document.querySelector('input[name="entry_type"]:checked');
        return selected ? selected.value : null;
    }

    let isOverTrigger = false;
    let isOverMenu = false;

    trigger.addEventListener('mouseenter', function () {
        isOverTrigger = true;
        if (getCurrentType() === 'case') {
            menu.style.display = 'block';
        }
    });

    trigger.addEventListener('mouseleave', function () {
        isOverTrigger = false;
        setTimeout(() => {
            if (!isOverMenu) menu.style.display = 'none';
        }, 200);
    });

    menu.addEventListener('mouseenter', function () {
        isOverMenu = true;
    });

    menu.addEventListener('mouseleave', function () {
        isOverMenu = false;
        setTimeout(() => {
            if (!isOverTrigger) menu.style.display = 'none';
        }, 200);
    });

    const radios = document.querySelectorAll('input[name="entry_type"]');
    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            menu.style.display = 'none';
        });
    });
});
</script>
<!-- 🔶 مودال جدول أعمال المحكمة -->
<div class="modal fade" id="courtScheduleModal" tabindex="-1" aria-labelledby="courtScheduleLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">جدول أعمال المحكمة</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- 🔹 خيارات الفلترة -->
        <div class="row mb-3">
          
          <div class="col-md-6">
            <label class="form-label">تاريخ الجلسة:</label>
            <input type="date" id="courtScheduleDate" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">حالة الجلسة:</label>
            <select id="courtScheduleStatus" class="form-select">
              <option value="">كل الحالات</option>
            </select>
          </div>

        </div>

        <div class="text-center mb-3">
          <button class="btn btn-primary" onclick="loadCourtSchedule()">بحث</button>
        </div>

        <!-- 🔹 جدول النتائج -->
        <div class="table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-light">
              <tr>
                <th>رقم الدعوى</th>
                <th>التاريخ</th>
                <th>الوقت</th>
                <th>نوع الجلسة</th>
                <th>حالة الجلسة</th>
                <th>اسم المحكمة</th>
                <th>اسم القاضي</th>
              </tr>
            </thead>
            <tbody id="courtScheduleTable">
              <tr><td colspan="7">لا توجد بيانات</td></tr>
            </tbody>
          </table>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

<script>

// فتح المودال
function openCourtScheduleModal() {
    const modal = new bootstrap.Modal(document.getElementById('courtScheduleModal'));
    modal.show();

    // تحميل الحالات فورًا
    loadSessionStatuses();
}


// ===========================================
// تحميل الحالات من المسار الصحيح
// ===========================================
function loadSessionStatuses() {
    fetch('/session-statuses-court')
        .then(res => res.json())
        .then(statuses => {
            const select = document.getElementById('courtScheduleStatus');
            select.innerHTML = '<option value="">كل الحالات</option>';

            statuses.forEach(s => {
                select.innerHTML += `<option value="${s}">${s}</option>`;
            });
        })
        .catch(() => {
            alert("تعذر تحميل حالات الجلسات");
        });
}


// ===========================================
// تحميل جدول المحكمة
// ===========================================
function loadCourtSchedule() {

    const params = {
        date: document.getElementById('courtScheduleDate').value,
        status: document.getElementById('courtScheduleStatus').value,
    };

    fetch('/court-schedule?' + new URLSearchParams(params))
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById("courtScheduleTable");
            tbody.innerHTML = "";

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="7">لا يوجد جلسات مطابقة</td></tr>`;
                return;
            }

            data.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.case_number ?? '-'}</td>
                        <td>${item.date}</td>
                        <td>${item.time}</td>
                        <td>${item.session_type ?? '-'}</td>
                        <td>${item.status ?? '-'}</td>
                        <td>${item.tribunal_name ?? '-'}</td>
                        <td>${item.judge_name ?? '-'}</td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            console.error(err);
            alert("حدث خطأ أثناء تحميل جدول المحكمة");
        });
}

</script>

<!-- 🔶 مودال جدول أعمال القاضي -->
<div class="modal fade" id="judgeScheduleModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">جدول أعمال القاضي</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- 🔹 فلاتر -->
        <div class="row mb-4">

          <!-- اختيار القاضي -->
          <div class="col-md-6">
            <label class="form-label">اختر القاضي:</label>
            <select id="judgeSelect" class="form-select">
              <option value="">اختر قاضٍ</option>
            </select>
          </div>

          <!-- حالة الجلسة -->
          <div class="col-md-6">
            <label class="form-label">حالة الجلسة:</label>
            <select id="judgeSessionStatus" class="form-select">
              <option value="">كل الحالات</option>
              <option value="محددة">محددة</option>
              <option value="مستمرة">مستمرة</option>
              <option value="مكتملة">مكتملة</option>
              <option value="مؤجلة">مؤجلة</option>
            </select>
          </div>

        </div>

        <div class="text-center mb-3">
          <button class="btn btn-primary" onclick="loadJudgeSchedule()">عرض الجدول</button>
        </div>

        <!-- 🔹 جدول النتائج -->
        <div class="table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-light">
              <tr>
                <th>رقم الدعوى</th>
                <th>تاريخ الجلسة</th>
                <th>وقت الجلسة</th>
                <th>المحكمة</th>
                <th>نوع الجلسة</th>
                <th>حالة الجلسة</th>
                <th>السبب</th>
                <th>التاريخ الأصلي</th>
              </tr>
            </thead>
            <tbody id="judgeScheduleTable">
              <tr><td colspan="8">لا توجد بيانات</td></tr>
            </tbody>
          </table>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>
<script>

/* ============================
   🔹 تحميل القضاة من السيرفر
============================ */
function loadJudges() {
    fetch('/judges')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById("judgeSelect");
            select.innerHTML = '<option value="">اختر قاضٍ</option>';

            data.forEach(j => {
                select.innerHTML += `<option value="${j.id}">${j.full_name}</option>`;
            });
        })
        .catch(() => alert("تعذر تحميل قائمة القضاة"));
}


/* ====================================================
   🔹 تحميل القضاة تلقائيًا عند فتح مودال جدول القاضي
==================================================== */
document.getElementById("judgeScheduleModal")
    .addEventListener("shown.bs.modal", function () {
        loadJudges();
    });




/* ============================
   🔹 تحميل جدول أعمال القاضي
============================ */
function loadJudgeSchedule() {

    const params = {
        judge_id: document.getElementById("judgeSelect").value,
        status: document.getElementById("judgeSessionStatus").value,
    };

    fetch('/judge-schedule?' + new URLSearchParams(params))
        .then(res => res.json())
        .then(data => {

            const tbody = document.getElementById("judgeScheduleTable");
            tbody.innerHTML = "";

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8">لا توجد جلسات مطابقة</td></tr>`;
                return;
            }

            data.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.case_number ?? '-'}</td>
                        <td>${item.date}</td>
                        <td>${item.time}</td>
                        <td>${item.tribunal_name ?? '-'}</td>
                        <td>${item.session_type ?? '-'}</td>
                        <td>${item.status ?? '-'}</td>
                        <td>${item.reason ?? '-'}</td>
                        <td>${item.original_date ?? '-'}</td>
                    </tr>
                `;
            });

        })
        .catch(err => {
            console.error(err);
            alert("حدث خطأ أثناء تحميل جدول أعمال القاضي");
        });
}

</script>

<!--  مودال تحديد جلسات الدعوى -->
<div class="modal fade" id="setCaseSessionModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <h5 class="modal-title">تحديد جلسات الدعوى</h5>
          <!-- ✅ إضافة معلومات رأس الصفحة -->
          <div class="text-end">
            <span class="me-3 fw-bold">رقم المحكمة: <span id="tribunalNumber">-</span></span>
            <span class="me-3 fw-bold">رقم القلم: <span id="departmentNumber">-</span></span>
            <span class="fw-bold">السنة: <span id="caseYear">-</span></span>
          </div>
        </div>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- 🔹 إدخال رقم الدعوى -->
        <div class="mb-3">
          <label class="form-label fw-bold">رقم الدعوى:</label>
          <input type="text" id="caseNumberInput" class="form-control" placeholder="أدخل رقم الدعوى">
        </div>

        <div class="text-center mb-4">
          <button class="btn btn-primary" onclick="loadCaseDetails()">بحث</button>
        </div>

        <!-- 🔹 جدول تفاصيل الدعوى -->
        <h5 class="mb-3 fw-bold">تفاصيل الدعوى</h5>

        <div class="table-responsive mb-4">
          <table class="table table-bordered text-center">
            <thead class="table-light">
              <tr>
                <th>رقم الدعوى</th>
                <th>نوع الدعوى</th>
                <th>القاضي</th>
                <th>الأطراف</th>
                <th>التاريخ الأصلي</th>
              </tr>
            </thead>
            <tbody id="caseDetailsTable">
              <tr><td colspan="5">لا توجد بيانات</td></tr>
            </tbody>
          </table>
        </div>

        <!-- 🔹 نموذج تحديد جلسة -->
        <h5 class="fw-bold mb-3">تحديد جلسة جديدة</h5>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">تاريخ الجلسة:</label>
            <input type="date" id="sessionDate" class="form-control">
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">وقت الجلسة:</label>
            <input type="time" id="sessionTime" class="form-control">
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">سبب الجلسة:</label>
            <input type="text" id="sessionGoal" class="form-control" placeholder="سبب تحديد الجلسة">
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">نوع الحكم:</label>
            <select id="judgmentType" class="form-control">
              <option value="تدقيقيا">تدقيقيا</option>
              <option value="ابتدائي">ابتدائي</option>
              <option value="غيابي">غيابي</option>
              <option value="وجاهي">وجاهي</option>
            </select>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label">حالة الجلسة:</label>
            <select id="sessionStatus" class="form-control">
              <option value="مفصولة">مفصولة</option>
              <option value="مستمرة">مستمرة</option>
              <option value="مكتملة">مكتملة</option>
              <option value="مؤجلة">مؤجلة</option>
            </select>
          </div>
        </div>

        <div class="text-center mt-3">
          <button class="btn btn-success" onclick="saveCaseSession()">حفظ الجلسة</button>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    console.log("🔥 JS Loaded Correctly");

    /* ============================================================
       🔹 تحميل تفاصيل الدعوى
    ============================================================ */
    window.loadCaseDetails = function () {

        console.log("🔥 loadCaseDetails() called!");

        const caseNumber = document.getElementById("caseNumberInput").value;

        if (!caseNumber) {
            alert("يرجى إدخال رقم الدعوى");
            return;
        }

        console.log("📌 Fetching:", `/typist/case-details/${caseNumber}`);

        fetch(`/typist/case-details/${caseNumber}`)
            .then(res => {
                console.log("📌 Raw Response:", res);
                return res.json();
            })
            .then(data => {

                console.log("📌 Parsed JSON:", data);

                if (data.error) {
                    alert(data.error);
                    return;
                }

                if (!data.id) {
                    alert("⚠️ السيرفر لم يرجع ID — مشكلة في الكنترولر");
                    return;
                }

                // 🔥 تخزين المعرّفات
                window.selectedCaseId  = Number(data.id);
                window.selectedJudgeId = Number(data.judge_id);

                console.log("🔥 Stored selectedCaseId =", window.selectedCaseId);
                console.log("🔥 Stored selectedJudgeId =", window.selectedJudgeId);

                let participants = data.participants?.length
                    ? data.participants.map(p => `${p.type}: ${p.name}`).join("<br>")
                    : "-";

                // ✅ تعبئة الجدول
                document.getElementById("caseDetailsTable").innerHTML = `
                    <tr>
                        <td>${data.case_number}</td>
                        <td>${data.case_type ?? '-'}</td>
                        <td>${data.judge_name ?? '-'}</td>
                        <td>${participants}</td>
                        <td>${data.created_at}</td>
                    </tr>
                `;

                // ✅ تعبئة رأس النافذة (رقم المحكمة والقلم والسنة)
                document.getElementById("tribunalNumber").textContent   = data.tribunal_number ?? '-';
                document.getElementById("departmentNumber").textContent = data.department_number ?? '-';
                document.getElementById("caseYear").textContent         = data.year ?? '-';

            })
            .catch(err => {
                console.error("❌ Fetch Error:", err);
                alert("حدث خطأ أثناء تحميل تفاصيل الدعوى");
            });
    };


    /* ============================================================
       🔹 حفظ الجلسة
    ============================================================ */
    window.saveCaseSession = function () {

        console.log("🔥 saveCaseSession() called!");

        // 🔥 فحص وصول المعرّفات
        if (!window.selectedCaseId) {
            alert("❌ لم يتم تحميل بيانات الدعوى بعد");
            return;
        }

        if (!window.selectedJudgeId) {
            alert("❌ لا يوجد قاضي مربوط بهذه الدعوى");
            return;
        }

        const sessionDate   = document.getElementById("sessionDate").value;
        const sessionTime   = document.getElementById("sessionTime").value;
        const sessionGoal   = document.getElementById("sessionGoal").value;
        const judgmentType  = document.getElementById("judgmentType").value;
        const sessionStatus = document.getElementById("sessionStatus").value;

        if (!sessionDate || !sessionTime || !sessionGoal) {
            alert("يرجى تعبئة جميع الحقول");
            return;
        }

        const payload = {
            court_case_id: window.selectedCaseId,
            judge_id: window.selectedJudgeId,
            session_date: `${sessionDate} ${sessionTime}:00`,
            session_time: sessionTime,
            session_goal: sessionGoal,
            judgment_type: judgmentType,
            status: sessionStatus
        };

        console.log("📤 Sending payload:", payload);

        fetch('/typist/set-session', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            console.log("📥 Raw Response from save:", res);
            return res.json();
        })
        .then(data => {

            console.log("📥 Parsed JSON from save:", data);

            if (data.errors) {
                alert("هناك أخطاء في البيانات");
                console.log(data.errors);
                return;
            }

            alert(data.message);
        })
        .catch(err => {
            console.error("❌ Save Error:", err);
            alert("حدث خطأ أثناء حفظ الجلسة");
        });
    };

});

</script>
@endpush




{{-- ✅ نافذة جدول الدعوى --}}
<div class="modal fade" id="caseScheduleModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">جدول الدعوى</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row mb-3">

          <div class="col-md-3">
            <label class="form-label">رقم المحكمة</label>
            <input type="text" id="cs_tribunal" class="form-control form-control-sm" value="---" readonly>
          </div>

          <div class="col-md-3">
            <label class="form-label">رقم القلم</label>
            <input type="text" id="cs_department" class="form-control form-control-sm" value="---" readonly>
          </div>

          <div class="col-md-3">
            <label class="form-label">السنة</label>
            <input type="text" class="form-control form-control-sm" value="{{ date('Y') }}" readonly>
          </div>

          <div class="col-md-3">
            <label class="form-label">رقم الدعوى</label>
            <input type="text" id="cs_case_number" class="form-control form-control-sm"
                   placeholder="أدخل رقم الدعوى">
          </div>

        </div>

        <div class="table-responsive mt-3">
          <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
              <tr>
                <th>تاريخ الجلسة</th>
                <th>وقت الجلسة</th>
                <th>نوع الحكم</th>
                <th>نوع الجلسة</th>
                <th>حالة الجلسة</th>
                <th>القاضي</th>
              </tr>
            </thead>

            <tbody id="cs_sessions_body">
              <tr><td colspan="6">يرجى إدخال رقم الدعوى لعرض الجلسات</td></tr>
            </tbody>

          </table>
        </div>

      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" onclick="closeCaseSchedule()">خروج</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('cs_case_number');
    const tbody = document.getElementById('cs_sessions_body');

    const tribunalInput = document.getElementById('cs_tribunal');
    const departmentInput = document.getElementById('cs_department');

    const caseScheduleUrlTemplate = @json(route('case.schedule', ['caseNumber' => 'CASE_NUMBER_PLACEHOLDER']));

    input.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;

        const caseNumber = input.value.trim();
        if (!caseNumber) {
            alert('يرجى إدخال رقم الدعوى');
            return;
        }

        const url = caseScheduleUrlTemplate.replace('CASE_NUMBER_PLACEHOLDER', encodeURIComponent(caseNumber));

        fetch(url)
            .then(response => response.json())
            .then(data => {

                tbody.innerHTML = '';

                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="6">${data.error}</td></tr>`;
                    tribunalInput.value = '---';
                    departmentInput.value = '---';
                    return;
                }

                tribunalInput.value = data.tribunal_number ?? '---';
                departmentInput.value = data.department_number ?? '---';

                if (!data.sessions || data.sessions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6">لا توجد جلسات لهذه الدعوى</td></tr>';
                    return;
                }

                data.sessions.forEach(s => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${s.session_date ?? '---'}</td>
                            <td>${s.session_time ?? '---'}</td>
                            <td>${s.judgment_type ?? '---'}</td>
                            <td>${s.session_type ?? '---'}</td>
                            <td>${s.status ?? '---'}</td>
                            <td>${s.judge_name ?? '---'}</td>
                        </tr>
                    `;
                });

            })
            .catch(err => {
                console.error('❌ خطأ:', err);
                alert('حدث خطأ أثناء تحميل الجلسات');
            });

    });
});

function closeCaseSchedule() {
  const modalEl = document.getElementById('caseScheduleModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) modal.hide();
}
</script>














































<style>
  /* 🔹 تحسين الترتيب */
  #caseScheduleModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
  }
  #caseScheduleModal .table th {
    white-space: nowrap;
  }
</style>


<!-- نافذة الأحكام -->
<!-- نافذة أحكام الدعوى -->

<!-- =========================== -->
<!-- 🔶 نافذة أحكام الدعوى -->
<!-- =========================== -->
<div class="modal fade" id="judgmentModal" tabindex="-1" aria-labelledby="judgmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">أحكام الدعوى</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- =========================== -->
        <!-- 🔹 البيانات الأساسية -->
        <!-- =========================== -->
        <div class="row mb-3">

          <div class="col-md-3">
            <label>رقم المحكمة:</label>
            <input type="text" id="tribunalNumber" class="form-control" readonly>
          </div>

          <div class="col-md-3">
            <label>القلم:</label>
            <input type="text" id="departmentNumber" class="form-control" readonly>
          </div>

          <div class="col-md-3">
            <label>السنة:</label>
            <input type="text" id="caseYear" class="form-control" readonly>
          </div>

          <div class="col-md-3">
            <label>رقم الدعوى:</label>
            <input type="text" id="caseNumberInputJudgment" class="form-control" placeholder="أدخل الرقم واضغط Enter">
          </div>

        </div>

        <!-- =========================== -->
        <!-- 🔹 تواريخ الحكم -->
        <!-- =========================== -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label>تاريخ الحكم:</label>
            <input type="date" id="judgmentDate" class="form-control">
          </div>

          <div class="col-md-6">
            <label>تاريخ الإغلاق:</label>
            <input type="date" id="closureDate" class="form-control">
          </div>
        </div>

        <!-- =========================== -->
        <!-- 🔘 أزرار الأقسام -->
        <!-- =========================== -->
        <div class="d-flex justify-content-between mb-4">
          <button class="btn btn-outline-primary" onclick="showSection('againstParties')">الحكم ضد الأطراف</button>
          <button class="btn btn-outline-success" onclick="showSection('finalJudgment')">الحكم الفاصل</button>
          <button class="btn btn-outline-danger" onclick="showSection('personalDrop')">إسقاط الحق الشخصي</button>
        </div>

        <!-- =========================== -->
        <!-- القسم الأول: الحكم ضد الأطراف -->
        <!-- =========================== -->
        <div id="againstParties" class="judgment-section" style="display:none;">

          <label>اختر الطرف:</label>
          <select id="participantAgainst" class="form-select mb-3">
            <option value="">-- اختر الطرف --</option>
          </select>

          <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-secondary" onclick="showSubSection('chargeSplit')">فصل التهمة</button>
            <button class="btn btn-secondary" onclick="showSubSection('judgmentText')">الحكم</button>
            <button class="btn btn-secondary" onclick="showSubSection('executionDetails')">تفاصيل التنفيذ</button>
          </div>

          <div id="chargeSplit" class="sub-section" style="display:none;">
            <p>التهمة: <strong id="chargeText">—</strong></p>

            <label>فصل التهمة:</label>
            <select id="chargeSplitType" class="form-select">
              <option value="">اختر</option>
              <option value="إدانة">إدانة</option>
              <option value="إحالة">إحالة</option>
              <option value="إسقاط بالعفو">إسقاط بالعفو</option>
            </select>
          </div>

          <div id="judgmentText" class="sub-section" style="display:none;">
            <label>نص الحكم:</label>
            <textarea id="judgmentTextInput" class="form-control" rows="3"></textarea>
          </div>

          <div id="executionDetails" class="sub-section" style="display:none;">
            <label>تفاصيل التنفيذ:</label>
            <textarea id="executionDetailsInput" class="form-control" rows="3"></textarea>
          </div>

        </div>

        <!-- =========================== -->
        <!-- القسم الثاني: الحكم الفاصل -->
        <!-- =========================== -->
        <div id="finalJudgment" class="judgment-section" style="display:none;">

          <label>كيفية إنهاء الدعوى:</label>
          <select id="terminationType" class="form-select mb-3">
            <option value="">اختر</option>
            <option>إحالة</option>
            <option>إدانة</option>
            <option>إسقاط بالعفو</option>
          </select>

          <label>اختر الطرف:</label>
          <select id="participantFinal" class="form-select mb-3">
            <option value="">-- اختر الطرف --</option>
          </select>

          <label>نوع الحكم:</label>
          <select id="judgmentType" class="form-select mb-3">
            <option value="">اختر</option>
            <option>بمثابة الوجاهي</option>
            <option>تدقيقيا</option>
            <option>غيابي</option>
            <option>وجاهي</option>
          </select>

          <label>خلاصة الحكم:</label>
          <textarea id="judgmentSummary" class="form-control" rows="3"></textarea>
        </div>

        <!-- =========================== -->
        <!-- القسم الثالث: إسقاط الحق الشخصي -->
        <!-- =========================== -->
        <div id="personalDrop" class="judgment-section" style="display:none;">
          <label>نص إسقاط الحق الشخصي:</label>
          <textarea id="personalDropText" class="form-control" rows="3"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        <button class="btn btn-primary" onclick="saveJudgment()">حفظ</button>
      </div>

    </div>
  </div>
</div>

<input type="hidden" id="courtCaseId">
<script>
// ===========================
// 🔥 جلب بيانات الدعوى
// ===========================
function fetchCaseData(caseNumber) {
    fetch(`/judgment/${caseNumber}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) return alert(data.error);

            window.loadedParticipants = data.participants || [];

            document.getElementById('courtCaseId').value = data.case.id;

            document.getElementById('tribunalNumber').value = data.case.tribunal?.number || '';
            document.getElementById('departmentNumber').value = data.case.department?.number || '';
            document.getElementById('caseYear').value = data.case.year || '';

            const selects = [document.getElementById('participantAgainst'), document.getElementById('participantFinal')];

            selects.forEach(sel => {
                sel.innerHTML = '<option value="">-- اختر الطرف --</option>';
                window.loadedParticipants.forEach(p => {
                    sel.innerHTML += `<option value="${p.id}">${p.type} - ${p.name}</option>`;
                });
            });

        });
}

// ===========================
// 🔥 إظهار الأقسام
// ===========================
window.showSection = function (id) {
    document.querySelectorAll('.judgment-section').forEach(el => el.style.display = 'none');
    document.getElementById(id).style.display = 'block';
    document.querySelectorAll('.sub-section').forEach(el => el.style.display = 'none');
};

window.showSubSection = function (id) {
    document.querySelectorAll('.sub-section').forEach(el => el.style.display = 'none');
    document.getElementById(id).style.display = 'block';
};

// ===========================
// 🔥 اختيار طرف → التهمة
// ===========================
document.addEventListener("change", function(e) {
    if (e.target.id === "participantAgainst") {
        const id = e.target.value;
        const p = window.loadedParticipants.find(x => x.id == id);
        document.getElementById('chargeText').textContent = p ? (p.charge || "—") : "—";
    }
});

// ===========================
// 🔥 زر الحفظ النهائي
// ===========================
function saveJudgment() {

    const payload = {
        court_case_id: document.getElementById('courtCaseId').value,

        participant_id:
            document.getElementById('participantAgainst').value ||
            document.getElementById('participantFinal').value ||
            null,

        judgment_date: document.getElementById('judgmentDate').value,
        closure_date: document.getElementById('closureDate').value,

        charge_split_type: document.getElementById('chargeSplitType')?.value,
        charge_text: document.getElementById('judgmentTextInput')?.value,
        execution_details: document.getElementById('executionDetailsInput')?.value,

        termination_type: document.getElementById('terminationType')?.value,
        judgment_type: document.getElementById('judgmentType')?.value,
        judgment_summary: document.getElementById('judgmentSummary')?.value,

        // 🔥 الجديد
        personal_drop_text: document.getElementById('personalDropText')?.value,
    };

    console.log("📤 PAYLOAD:", payload);

    fetch("/typist/judgment/save", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert("خطأ: " + data.error);
        } else {
            alert(data.message || "تم الحفظ بنجاح");
        }
    })
    .catch(() => alert("❌ فشل الحفظ"));
}

// ===========================
// Enter لتحميل الدعوى
// ===========================
document.getElementById('caseNumberInputJudgment').addEventListener("keydown", function(e) {
    if (e.key === "Enter") fetchCaseData(this.value.trim());
});
</script>



















<!-- نافذه إعادة تحديد الجلسات-->
<!-- نافذة إعادة التحديد -->
<div class="modal fade" id="rescheduleSessionModal" tabindex="-1" aria-labelledby="rescheduleSessionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <h5 class="modal-title">إعادة تحديد جلسات الدعوى</h5>
          <!-- ✅ إضافة معلومات رأس الصفحة -->
          <div class="text-end">
            <span class="me-3 fw-bold">رقم المحكمة: <span id="rescheduleTribunalNumber">-</span></span>
            <span class="me-3 fw-bold">رقم القلم: <span id="rescheduleDepartmentNumber">-</span></span>
            <span class="fw-bold">السنة: <span id="rescheduleCaseYear">-</span></span>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <div class="modal-body">

        <!-- إدخال رقم الدعوى -->
        <div class="mb-3">
          <label>رقم الدعوى:</label>
          <input type="text" id="caseNumberInputReschedule" class="form-control" placeholder="أدخل رقم الدعوى واضغط Enter">
        </div>

        <!-- جدول تفاصيل الدعوى -->
        <div id="caseDetailsTableReschedule" class="mb-4">
          <table class="table table-bordered table-sm text-center">
            <thead class="table-light">
              <tr>
                <th>رقم الدعوى</th>
                <th>نوع الدعوى</th>
                <th>القاضي</th>
                <th>الأطراف</th>
                <th>التاريخ الأصلي</th>
              </tr>
            </thead>
            <tbody id="caseDetailsBodyReschedule">
              <tr><td colspan="5">لا توجد بيانات</td></tr>
            </tbody>
          </table>
        </div>

        <!-- تفاصيل الجلسة القديمة -->
        <div id="oldSessionDetails" class="mb-4">
          <h6 class="fw-bold">الجلسة القديمة</h6>
          <table class="table table-bordered table-sm text-center">
            <thead class="table-light">
              <tr>
                <th>التاريخ</th>
                <th>الوقت</th>
                <th>السبب</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody id="oldSessionBody">
              <tr><td colspan="4">لا توجد بيانات</td></tr>
            </tbody>
          </table>
        </div>

        <!-- نموذج الجلسة الجديدة -->
        <div id="newSessionForm">
          <h6 class="fw-bold">إدخال الجلسة الجديدة</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label>تاريخ الجلسة:</label>
              <input type="date" id="newSessionDate" class="form-control">
            </div>
            <div class="col-md-4">
              <label>وقت الجلسة:</label>
              <input type="time" id="newSessionTime" class="form-control">
            </div>
            <div class="col-md-4">
              <label>سبب الجلسة:</label>
              <input type="text" id="newSessionGoal" class="form-control" placeholder="سبب الجلسة">
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label>نوع الحكم:</label>
              <select id="newJudgmentType" class="form-control">
                <option value="تدقيقيا">تدقيقيا</option>
                <option value="ابتدائي">ابتدائي</option>
                <option value="غيابي">غيابي</option>
                <option value="وجاهي">وجاهي</option>
              </select>
            </div>

            <div class="col-md-4">
              <label>حالة الجلسة:</label>
              <select id="newSessionStatus" class="form-control">
                <option value="مفصولة">مفصولة</option>
                <option value="مستمرة">مستمرة</option>
                <option value="مكتملة">مكتملة</option>
                <option value="مؤجلة">مؤجلة</option>
              </select>
            </div>
          </div>

          <div class="mt-3 text-center">
            <button class="btn btn-primary" onclick="rescheduleSession()">إعادة التحديد</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<script>
let currentCaseId = null;
let currentJudgeId = null;
let currentSessionId = null;

/* ===============================
   🔹 عند إدخال رقم الدعوى والضغط Enter
================================= */
document.getElementById('caseNumberInputReschedule').addEventListener('keypress', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const caseNumber = this.value.trim();
    if (caseNumber) {
      fetchCaseDetailsAndSession(caseNumber);
    }
  }
});

/* ===============================
   🔹 جلب تفاصيل الدعوى + الجلسة القديمة
================================= */
function fetchCaseDetailsAndSession(caseNumber) {
  fetch(`/typist/case-details/${caseNumber}`)
    .then(res => res.json())
    .then(caseData => {
      currentCaseId = caseData.id;
      currentJudgeId = caseData.judge_id;
      renderCaseDetails(caseData);

      // ✅ تعبئة رأس النافذة
      document.getElementById("rescheduleTribunalNumber").textContent   = caseData.tribunal_number ?? '-';
      document.getElementById("rescheduleDepartmentNumber").textContent = caseData.department_number ?? '-';
      document.getElementById("rescheduleCaseYear").textContent         = caseData.year ?? '-';

      fetchOldSession(caseNumber);
    })
    .catch(() => alert('❌ رقم الدعوى غير موجود'));
}

/* ===============================
   🔹 عرض تفاصيل الدعوى في الجدول
================================= */
function renderCaseDetails(caseData) {
  const tbody = document.getElementById('caseDetailsBodyReschedule');
  const participants = caseData.participants?.length
    ? caseData.participants.map(p => `${p.type}: ${p.name}`).join('<br>')
    : '-';

  tbody.innerHTML = `
    <tr>
      <td>${caseData.case_number}</td>
      <td>${caseData.case_type ?? '-'}</td>
      <td>${caseData.judge_name ?? '-'}</td>
      <td>${participants}</td>
      <td>${caseData.created_at ?? '-'}</td>
    </tr>
  `;
}

/* ===============================
   🔹 جلب الجلسة القديمة
================================= */
function fetchOldSession(caseNumber) {
  fetch(`/typist/get-session/${caseNumber}`)
    .then(res => res.json())
    .then(session => {
      currentSessionId = session.id;
      renderOldSession(session);
    })
    .catch(() => {
      document.getElementById('oldSessionBody').innerHTML = `
        <tr><td colspan="4" class="text-center text-muted">لا توجد جلسة محددة</td></tr>
      `;
    });
}

/* ===============================
   🔹 عرض الجلسة القديمة
================================= */
function renderOldSession(session) {
  const tbody = document.getElementById('oldSessionBody');
  tbody.innerHTML = `
    <tr>
      <td>${session.session_date}</td>
      <td>${session.session_time}</td>
      <td>${session.session_goal}</td>
      <td><button class="btn btn-danger btn-sm" onclick="deleteOldSession()">حذف</button></td>
    </tr>
  `;
}

/* ===============================
   🔹 حذف الجلسة القديمة
================================= */
function deleteOldSession() {
  fetch(`/typist/delete-case-session/${currentSessionId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(res => res.json())
    .then(() => {
      alert('✅ تم حذف الجلسة');
      document.getElementById('oldSessionBody').innerHTML = `
        <tr><td colspan="4" class="text-center text-success">تم حذف الجلسة</td></tr>
      `;
    })
    .catch(() => alert('❌ فشل حذف الجلسة'));
}

/* ===============================
   🔹 حفظ الجلسة الجديدة (مع نوع الحكم + حالة الجلسة)
================================= */
function rescheduleSession() {
  const date = document.getElementById('newSessionDate').value;
  const time = document.getElementById('newSessionTime').value;
  const goal = document.getElementById('newSessionGoal').value;

  const judgmentType = document.getElementById('newJudgmentType').value;
  const sessionStatus = document.getElementById('newSessionStatus').value;

  if (!date || !time || !goal) {
    alert('❌ يرجى تعبئة جميع الحقول');
    return;
  }

  const payload = {
    court_case_id: currentCaseId,
    judge_id: currentJudgeId,
    session_date: `${date} ${time}:00`,
    session_time: time,
    session_goal: goal,
    judgment_type: judgmentType,
    status: sessionStatus,
    end: false
  };

  fetch('/typist/set-session', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message || '✅ تم إعادة تحديد الجلسة بنجاح');
    })
    .catch(() => alert('❌ فشل حفظ الجلسة الجديدة'));
}
</script>
























<!-- نافذة إلغاء الجلسة -->
<div class="modal fade" id="cancelSessionModal" tabindex="-1" aria-labelledby="cancelSessionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="w-100">
          <h5 class="modal-title mb-3">إلغاء جلسات الدعوى</h5>
          <div class="row g-3">
            <div class="col-md-3">
              <label>رقم المحكمة:</label>
              <input type="text" id="tribunalNumberCancel" class="form-control" disabled>
            </div>
            <div class="col-md-3">
              <label>رقم القلم:</label>
              <input type="text" id="departmentNumberCancel" class="form-control" disabled>
            </div>
            <div class="col-md-3">
              <label>السنة:</label>
              <input type="text" id="caseYearCancel" class="form-control" disabled>
            </div>
            <div class="col-md-3">
              <label>رقم الدعوى:</label>
              <input type="text" id="caseNumberInputCancel" class="form-control" placeholder="أدخل رقم الدعوى واضغط Enter">
            </div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <div class="modal-body">

        <!-- جدول تفاصيل الدعوى -->
        <div id="caseDetailsTableCancel" class="mb-4">
          <table class="table table-bordered table-sm">
            <thead class="table-light">
              <tr>
                <th>رقم الدعوى</th>
                <th>نوع الدعوى</th>
                <th>القاضي</th>
                <th>الأطراف</th>
                <th>التاريخ الأصلي</th>
              </tr>
            </thead>
            <tbody id="caseDetailsBodyCancel">
              <!-- يتم تعبئته من JavaScript -->
            </tbody>
          </table>
        </div>

        <!-- تفاصيل الجلسة الحالية -->
        <div id="cancelSessionDetails">
          <h6>موعد الجلسة</h6>
          <table class="table table-bordered table-sm">
            <thead class="table-light">
              <tr>
                <th>التاريخ</th>
                <th>الوقت</th>
                <th>السبب</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody id="cancelSessionBody">
              <!-- يتم تعبئته من JavaScript -->
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
<script>
  let cancelCaseId = null;
let cancelSessionId = null;

// إدخال رقم الدعوى
document.getElementById('caseNumberInputCancel').addEventListener('keypress', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const caseNumber = this.value.trim();
    if (caseNumber) {
      fetchCancelCaseDetails(caseNumber);
    }
  }
});

// ✅ جلب تفاصيل الدعوى
function fetchCancelCaseDetails(caseNumber) {
  fetch(`/typist/cancel-case-details/${caseNumber}`)
    .then(res => res.json())
    .then(caseData => {
      cancelCaseId = caseData.id;
      document.getElementById('tribunalNumberCancel').value = caseData.tribunal_number || '';
      document.getElementById('departmentNumberCancel').value = caseData.department_number || '';
      document.getElementById('caseYearCancel').value = caseData.year || '';
      renderCancelCaseDetails(caseData);
      fetchCancelSession(caseNumber);
    })
    .catch(() => alert('❌ رقم الدعوى غير موجود'));
}

// ✅ عرض تفاصيل الدعوى
function renderCancelCaseDetails(caseData) {
  const tbody = document.getElementById('caseDetailsBodyCancel');
  const participants = caseData.participants.map(p => `${p.type}: ${p.name}`).join('<br>');
  tbody.innerHTML = `
    <tr>
      <td>${caseData.case_number}</td>
      <td>${caseData.case_type}</td>
      <td>${caseData.judge_name}</td>
      <td>${participants}</td>
      <td>${caseData.created_at}</td>
    </tr>
  `;
}

// ✅ جلب الجلسة الحالية
function fetchCancelSession(caseNumber) {
  fetch(`/typist/cancel-session/${caseNumber}`)
    .then(res => res.json())
    .then(session => {
      cancelSessionId = session.id;
      renderCancelSession(session);
    })
    .catch(() => {
      document.getElementById('cancelSessionBody').innerHTML = `
        <tr><td colspan="4" class="text-center text-muted">لا توجد جلسة حالية</td></tr>
      `;
    });
}

// ✅ عرض الجلسة مع زر إلغاء
function renderCancelSession(session) {
  const tbody = document.getElementById('cancelSessionBody');
  tbody.innerHTML = `
    <tr>
      <td>${session.session_date}</td>
      <td>${session.session_time}</td>
      <td>${session.session_goal}</td>
      <td><button class="btn btn-danger btn-sm" onclick="cancelSession()">إلغاء الجلسة</button></td>
    </tr>
  `;
}

// ✅ حذف الجلسة
function cancelSession() {
  fetch(`/typist/cancel-session-delete/${cancelSessionId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message || '✅ تم إلغاء الجلسة');
      document.getElementById('cancelSessionBody').innerHTML = `
        <tr><td colspan="4" class="text-center text-success">✅ تم إلغاء الجلسة</td></tr>
      `;
    })
    .catch(() => alert('❌ فشل إلغاء الجلسة'));
}
</script>


























































































{{-- ✅ قائمة الطلبات الخاصة بالطابعة (تظهر عند المرور على الكلمة الموجودة في layouts.app) --}}
<div id="sessions-menu-request" class="position-absolute bg-white border rounded shadow-sm px-2 py-1"
     style="display: none; top: 38px; right: 12px; z-index: 1000; min-width: 220px;">
    <div class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#requestScheduleModal">جدول الطلبات</div>
    <div class="dropdown-item" onclick="openRequestSetSessionModal()">تحديد جلسات الطلبات</div>
    <div class="dropdown-item" onclick="openRequestRescheduleModal()">إعادة تحديد جلسات الطلبات</div>
    <div class="dropdown-item" onclick="openCancelRequestModal()">إلغاء جلسات الطلبات</div>
    <div class="dropdown-item" onclick="openRequestJudgmentModal()">أحكام الطلبات</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.getElementById('sessions-trigger');
    const menu = document.getElementById('sessions-menu-request');

    function getCurrentType() {
        const selected = document.querySelector('input[name="entry_type"]:checked');
        return selected ? selected.value : null;
    }

    let isOverTrigger = false;
    let isOverMenu = false;

    trigger.addEventListener('mouseenter', function () {
        isOverTrigger = true;
        if (getCurrentType() === 'request') {
            menu.style.display = 'block';
        }
    });

    trigger.addEventListener('mouseleave', function () {
        isOverTrigger = false;
        setTimeout(() => {
            if (!isOverMenu) menu.style.display = 'none';
        }, 200);
    });

    menu.addEventListener('mouseenter', function () {
        isOverMenu = true;
    });

    menu.addEventListener('mouseleave', function () {
        isOverMenu = false;
        setTimeout(() => {
            if (!isOverTrigger) menu.style.display = 'none';
        }, 200);
    });

    const radios = document.querySelectorAll('input[name="entry_type"]');
    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            menu.style.display = 'none';
        });
    });
});
</script>
<!-- ✅ نافذة جدول الطلبات -->
<div class="modal fade" id="requestScheduleModal" tabindex="-1" aria-labelledby="requestScheduleLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <!-- رأس النافذة -->
      <div class="modal-header">
        <h5 class="modal-title" id="requestScheduleLabel">جدول الطلبات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <!-- جسم النافذة -->
      <div class="modal-body">

        <!-- معلومات المحكمة -->
        <div class="mb-3">
          <label class="form-label">رقم المحكمة:</label>
          <span id="courtNumber">-</span>
        </div>
        <div class="mb-3">
          <label class="form-label">القلم:</label>
          <span id="courtDesk">-</span>
        </div>
        <div class="mb-3">
          <label class="form-label">السنة:</label>
          <span id="courtYear">-</span>
        </div>

        <!-- إدخال رقم الطلب -->
        <div class="mb-4">
          <label for="requestNumberInput" class="form-label">رقم الطلب:</label>
          <input type="text" class="form-control" id="requestNumberInput" placeholder="أدخل رقم الطلب" onkeydown="if(event.key === 'Enter') fetchRequestSchedule()">
        </div>

        <!-- جدول الجلسات -->
        <div class="table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-light">
              <tr>
                <th>تاريخ الجلسة</th>
                <th>وقت الجلسة</th>
                <th>حالة الجلسة</th>
                <th>السبب</th>
                <th>التاريخ الأصلي</th>
                <th>القاضي</th>
              </tr>
            </thead>
            <tbody id="requestSessionsBody">
              <tr>
                <td colspan="6">-</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- زر الإغلاق -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

<script>
function fetchRequestSchedule() {
    const requestNumber = document.getElementById('requestNumberInput').value;

    if (!requestNumber) {
        alert('يرجى إدخال رقم الطلب');
        return;
    }

    fetch('/typist/request-schedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ request_number: requestNumber })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateScheduleTable(data.data);

            if (data.data.length > 0) {
                const first = data.data[0];
                document.getElementById('courtNumber').textContent = first.tribunal_number || '-';
                document.getElementById('courtDesk').textContent = first.department_number || '-';
                document.getElementById('courtYear').textContent = first.court_year || '-';
            }
        } else {
            alert('لم يتم العثور على بيانات');
        }
    })
    .catch(error => {
        console.error('خطأ في الجلب:', error);
        alert('حدث خطأ أثناء جلب البيانات');
    });
}

function updateScheduleTable(sessions) {
    const tbody = document.getElementById('requestSessionsBody');
    tbody.innerHTML = ''; // مسح المحتوى السابق

    if (sessions.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6">لا توجد جلسات لهذا الطلب</td></tr>`;
        return;
    }

    sessions.forEach(session => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${session.session_date || '-'}</td>
            <td>${session.session_time || '-'}</td>
            <td>${session.session_status || '-'}</td>
            <td>${session.session_reason || '-'}</td>
            <td>${session.original_date || '-'}</td>
            <td>${session.judge_name || '-'}</td>
        `;
        tbody.appendChild(row);
    });
}
</script>





<!-- ✅ نافذة تحديد جلسة الطلب -->
<!-- ✅ نافذة تحديد جلسة الطلب -->
<div class="modal fade" id="requestSetSessionModal" tabindex="-1" aria-labelledby="requestSetSessionLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="margin-top: 80px;">
    <div class="modal-content">

      <!-- رأس النافذة -->
      <div class="modal-header">
        <h5 class="modal-title" id="requestSetSessionLabel">تحديد جلسة الطلب</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <!-- جسم النافذة -->
      <div class="modal-body">
        <!-- إدخال رقم الطلب -->
        <div class="mb-3">
          <label class="form-label">رقم الطلب:</label>
          <input type="text" class="form-control form-control-sm" id="request_session_number_input" placeholder="أدخل رقم الطلب واضغط Enter">
        </div>

        <form id="request-set-session-form" class="row g-3" method="POST" action="{{ route('typist.request.store-session') }}">
          @csrf
          <input type="hidden" name="id">

          <!-- جدول المحكمة -->
          <div class="col-12">
            <table class="table table-sm table-bordered">
              <tr>
                <th>رقم المحكمة</th>
                <td class="tribunal-number"></td>
                <th>رقم القلم</th>
                <td class="department-number"></td>
                <th>السنة</th>
                <td class="court-year"></td>
              </tr>
            </table>
          </div>

          <!-- جدول تفاصيل الطلب -->
          <div class="col-12">
            <table class="table table-bordered table-sm">
              <thead>
                <tr>
                  <th>رقم الدعوى</th>
                  <th>عنوان الطلب</th>
                  <th>المدعي</th>
                  <th>المدعى عليه</th>
                  <th>الطرف الثالث</th>
                  <th>التاريخ الأصلي</th>
                  <th>اسم القاضي</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="request-number"></td>
                  <td class="title"></td>
                  <td class="plaintiff"></td>
                  <td class="defendant"></td>
                  <td class="third-party"></td>
                  <td class="original-date"></td>
                  <td class="judge-name"></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- نموذج تحديد الجلسة -->
          <div class="session-form-fields row g-3">
            <div class="col-md-6">
              <label class="form-label">تاريخ الجلسة:</label>
              <input type="date" class="form-control form-control-sm" name="session_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">وقت الجلسة:</label>
              <input type="time" class="form-control form-control-sm" name="session_time" required>
            </div>
            <div class="col-12">
              <label class="form-label">سبب الجلسة:</label>
              <textarea class="form-control form-control-sm" name="session_reason" rows="2" required></textarea>
            </div>
            <!-- ✅ إضافة حالة الجلسة -->
            <div class="col-md-6">
              <label class="form-label">حالة الجلسة:</label>
              <select class="form-control form-control-sm" name="session_status" required>
                  <option value="">-- اختر الحالة --</option>
                  <option value="مستمرة">مستمرة</option>
                  <option value="مفصولة">مفصولة</option>
                  <option value="مكتملة">مكتملة</option>
                  <option value="مؤجلة">مؤجلة</option>
              </select>
            </div>
          </div>

          <!-- إذا الجلسة محددة مسبقًا -->
          <div class="session-warning d-none col-12">
            <div class="alert alert-warning">
              تم تحديد جلسة مسبقًا لهذا الطلب:
              <span class="session-date"></span> - <span class="session-time"></span>
              <br>
              <strong>الحالة:</strong> <span class="session-status"></span>
            </div>
          </div>
        </form>
      </div>

      <!-- أزرار -->
      <div class="modal-footer d-flex justify-content-between">
        <div></div>
        <div class="session-buttons">
          <button type="submit" form="request-set-session-form" class="btn btn-primary btn-sm">حفظ الجلسة</button>
          <button type="submit" form="request-set-session-form" name="finish" value="1" class="btn btn-success btn-sm">حفظ وإنهاء</button>
        </div>
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">خروج</button>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const modalElement = document.getElementById('requestSetSessionModal');
  const modal = new bootstrap.Modal(modalElement);

  const form = modalElement.querySelector('#request-set-session-form');
  const sessionFields = modalElement.querySelector('.session-form-fields');
  const sessionWarning = modalElement.querySelector('.session-warning');
  const sessionButtons = modalElement.querySelector('.session-buttons');
  const requestInput = modalElement.querySelector('#request_session_number_input');

  // جلب التفاصيل
  function fetchAndFillRequestModal(requestNumber) {
    if (!requestNumber) return;

    fetch(`/typist/request/${requestNumber}/details`)
      .then(response => response.json())
      .then(data => {
        form.querySelector('[name="id"]').value = data.id;

        modalElement.querySelector('.tribunal-number').textContent = data.tribunal_number || '';
        modalElement.querySelector('.department-number').textContent = data.department_number || '';
        modalElement.querySelector('.court-year').textContent = data.court_year || '';

        modalElement.querySelector('.request-number').textContent = data.request_number || '';
        modalElement.querySelector('.title').textContent = data.title || '';
        modalElement.querySelector('.plaintiff').textContent = data.plaintiff || '';
        modalElement.querySelector('.defendant').textContent = data.defendant || '';
        modalElement.querySelector('.third-party').textContent = data.third_party || '';
        modalElement.querySelector('.original-date').textContent = data.original_date || '';
        modalElement.querySelector('.judge-name').textContent = data.judge_name || '';

        if (!data.session_date && !data.session_time) {
          sessionFields.classList.remove('d-none');
          sessionButtons.classList.remove('d-none');
          sessionWarning.classList.add('d-none');
        } else {
          sessionFields.classList.add('d-none');
          sessionButtons.classList.add('d-none');
          sessionWarning.classList.remove('d-none');

          modalElement.querySelector('.session-date').textContent = data.session_date;
          modalElement.querySelector('.session-time').textContent = data.session_time;
          modalElement.querySelector('.session-status').textContent = data.session_status || '';
        }

        // ✅ إذا الحالة موجودة مسبقًا، نملأ الـ select
        if (data.session_status) {
          form.querySelector('[name="session_status"]').value = data.session_status;
        }
      })
      .catch(err => console.error('Error:', err));
  }

  // enter key
  requestInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      fetchAndFillRequestModal(requestInput.value.trim());
    }
  });

  // فتح النافذة من القائمة
  window.openRequestSetSessionModal = function () {
    modal.show();
  };

});
</script>










<!-- نافذه إعادة تحديد جلسات الطلبات-->
 <div class="modal fade" id="requestRescheduleModal" tabindex="-1" aria-labelledby="requestRescheduleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="margin-top: 80px;">
    <div class="modal-content">

      <!-- رأس -->
      <div class="modal-header">
        <h5 class="modal-title" id="requestRescheduleLabel">إعادة تحديد جلسة الطلب</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- جسم -->
      <div class="modal-body">
        <!-- إدخال رقم الطلب -->
        <div class="mb-3">
          <label class="form-label">رقم الطلب:</label>
          <input type="text" class="form-control form-control-sm" id="reschedule_request_number_input" placeholder="أدخل رقم الطلب واضغط Enter">
        </div>

        <form id="request-reschedule-session-form" class="row g-3" method="POST" action="{{ route('typist.request.reschedule-session') }}">
          @csrf
          <input type="hidden" name="id">

          <!-- جدول المحكمة -->
          <div class="col-12">
            <table class="table table-sm table-bordered">
              <tr>
                <th>رقم المحكمة</th>
                <td class="tribunal-number-res"></td>
                <th>رقم القلم</th>
                <td class="department-number-res"></td>
                <th>السنة</th>
                <td class="court-year-res"></td>
              </tr>
            </table>
          </div>

          <!-- جدول تفاصيل الطلب -->
          <div class="col-12">
            <table class="table table-bordered table-sm">
              <thead>
                <tr>
                  <th>رقم الدعوى</th>
                  <th>عنوان الطلب</th>
                  <th>المدعي</th>
                  <th>المدعى عليه</th>
                  <th>الطرف الثالث</th>
                  <th>التاريخ الأصلي</th>
                  <th>اسم القاضي</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="request-number-res"></td>
                  <td class="title-res"></td>
                  <td class="plaintiff-res"></td>
                  <td class="defendant-res"></td>
                  <td class="third-party-res"></td>
                  <td class="original-date-res"></td>
                  <td class="judge-name-res"></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- الجلسة القديمة -->
          <div class="col-12">
            <div class="alert alert-secondary d-flex justify-content-between align-items-center">
              <div>
                <strong>الجلسة الحالية:</strong>
                <span class="session-date-res"></span> - <span class="session-time-res"></span>
                <br>
                <strong>الحالة:</strong> <span class="session-status-res"></span>
              </div>
              <button type="button" class="btn btn-danger btn-sm" id="delete_reschedule_session_button">حذف الجلسة القديمة</button>
            </div>
          </div>

          <!-- نموذج إعادة التحديد -->
          <div class="reschedule-fields row g-3">
            <div class="col-md-6">
              <label class="form-label">تاريخ جديد للجلسة:</label>
              <input type="date" class="form-control form-control-sm" name="session_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">وقت جديد للجلسة:</label>
              <input type="time" class="form-control form-control-sm" name="session_time" required>
            </div>
            <div class="col-12">
              <label class="form-label">سبب إعادة التحديد:</label>
              <textarea class="form-control form-control-sm" name="session_reason" rows="2" required></textarea>
            </div>
            <!-- ✅ إضافة حالة الجلسة -->
            <div class="col-md-6">
              <label class="form-label">حالة الجلسة:</label>
              <select class="form-control form-control-sm" name="session_status" required>
                  <option value="">-- اختر الحالة --</option>
                  <option value="مستمرة">مستمرة</option>
                  <option value="مفصولة">مفصولة</option>
                  <option value="مكتملة">مكتملة</option>
                  <option value="مؤجلة">مؤجلة</option>
              </select>
            </div>
          </div>

        </form>
      </div>

      <!-- أزرار -->
      <div class="modal-footer d-flex justify-content-between">
        <div></div>
        <div>
          <button type="button" id="save_reschedule_session_button" class="btn btn-primary btn-sm"> إعادة تحديد الجلسة</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">خروج</button>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const modalElement = document.getElementById('requestRescheduleModal');
  const modal = new bootstrap.Modal(modalElement);

  const form = modalElement.querySelector('#request-reschedule-session-form');
  const requestInput = modalElement.querySelector('#reschedule_request_number_input');

  // فتح النافذة
  window.openRequestRescheduleModal = function () {
    modal.show();
    form.reset();
    modalElement.querySelectorAll('span').forEach(span => span.textContent = '');
  };

  // جلب التفاصيل
  function loadRescheduleDetails(requestNumber) {
    fetch(`/typist/reschedule/${requestNumber}/details`)
      .then(res => res.json())
      .then(data => {
        form.querySelector('[name="id"]').value = data.id;

        modalElement.querySelector('.tribunal-number-res').textContent = data.tribunal_number || '';
        modalElement.querySelector('.department-number-res').textContent = data.department_number || '';
        modalElement.querySelector('.court-year-res').textContent = data.court_year || '';

        modalElement.querySelector('.request-number-res').textContent = data.request_number || '';
        modalElement.querySelector('.title-res').textContent = data.title || '';
        modalElement.querySelector('.plaintiff-res').textContent = data.plaintiff || '';
        modalElement.querySelector('.defendant-res').textContent = data.defendant || '';
        modalElement.querySelector('.third-party-res').textContent = data.third_party || '';
        modalElement.querySelector('.original-date-res').textContent = data.original_date || '';
        modalElement.querySelector('.judge-name-res').textContent = data.judge_name || '';

        modalElement.querySelector('.session-date-res').textContent = data.session_date || 'غير محدد';
        modalElement.querySelector('.session-time-res').textContent = data.session_time || 'غير محدد';
        modalElement.querySelector('.session-status-res').textContent = data.session_status || 'غير محدد';

        // ✅ إذا الحالة موجودة مسبقًا، نملأ الـ select
        if (data.session_status) {
          form.querySelector('[name="session_status"]').value = data.session_status;
        }
      })
      .catch(err => console.error("خطأ:", err));
  }

  // عند Enter
  requestInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (requestInput.value.trim()) {
        loadRescheduleDetails(requestInput.value.trim());
      }
    }
  });

  // زر حذف الجلسة القديمة
  document.getElementById('delete_reschedule_session_button').addEventListener('click', function () {
    const requestId = form.querySelector('[name="id"]').value;
    if (!requestId) return;
    if (!confirm("هل أنت متأكد من حذف الجلسة؟")) return;

    fetch(`{{ route('typist.request.delete-session') }}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ id: requestId })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.success || "تم حذف الجلسة");
      modalElement.querySelector('.session-date-res').textContent = "";
      modalElement.querySelector('.session-time-res').textContent = "";
      modalElement.querySelector('.session-status-res').textContent = "";
    });
  });

  // ⭐ زر حفظ إعادة التحديد (AJAX)
  document.getElementById('save_reschedule_session_button').addEventListener('click', function () {
      const requestId = form.querySelector('[name="id"]').value;
      const sessionDate = form.querySelector('[name="session_date"]').value;
      const sessionTime = form.querySelector('[name="session_time"]').value;
      const sessionReason = form.querySelector('[name="session_reason"]').value;
      const sessionStatus = form.querySelector('[name="session_status"]').value;

      if (!requestId) {
          alert("رقم الطلب غير موجود");
          return;
      }

      fetch(`{{ route('typist.request.reschedule-session') }}`, {
          method: "POST",
          headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
              id: requestId,
              session_date: sessionDate,
              session_time: sessionTime,
              session_reason: sessionReason,
              session_status: sessionStatus   // ✅ إرسال الحالة الجديدة
          })
      })
      .then(res => res.json())
      .then(data => {
          alert(data.success || "تم حفظ موعد الجلسة الجديد");
          // ❗ إذا بدك النافذة تسكّر بعد الحفظ شغّلي هذا السطر:
          // modal.hide();
      })
      .catch(err => console.error("Error:", err));
  });

});
</script>


<!-- نافذة إلغاء جلسات الطلبات -->
<div class="modal fade" id="cancelRequestSessionModal" tabindex="-1" aria-labelledby="cancelRequestSessionLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="margin-top: 80px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="cancelRequestSessionLabel">إلغاء جلسة الطلب</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- رقم الطلب -->
        <div class="mb-3">
          <label class="form-label">رقم الطلب:</label>
          <input type="text" class="form-control form-control-sm" id="cancelRequestNumberInput" placeholder="أدخل رقم الطلب واضغط Enter">
        </div>

        <form id="cancel-request-session-form" method="POST">
          @csrf
          <input type="hidden" name="id">

          <!-- بيانات المحكمة -->
          <table class="table table-sm table-bordered mb-3">
            <tr>
              <th>رقم المحكمة</th>
              <td class="tribunal-number-request"></td>

              <th>رقم القلم</th>
              <td class="department-number-request"></td>

              <th>السنة</th>
              <td class="court-year-request"></td>
            </tr>
          </table>

          <!-- تفاصيل الطلب -->
          <table class="table table-bordered table-sm mb-3">
            <thead>
              <tr>
                <th>رقم الطلب</th>
                <th>عنوان الطلب</th>
                <th>المدعي</th>
                <th>المدعى عليه</th>
                <th>الطرف الثالث</th>
                <th>التاريخ الأصلي</th>
                <th>اسم القاضي</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="request-number-request"></td>
                <td class="title-request"></td>
                <td class="plaintiff-request"></td>
                <td class="defendant-request"></td>
                <td class="third-party-request"></td>
                <td class="original-date-request"></td>
                <td class="judge-name-request"></td>
              </tr>
            </tbody>
          </table>

          <!-- الجلسة الحالية -->
          <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <div>
              <strong>الجلسة الحالية:</strong>
              <span class="session-date-request"></span> -
              <span class="session-time-request"></span>
            </div>

            <button type="button" id="cancel-session-request-button" class="btn btn-danger btn-sm">
              إلغاء الجلسة
            </button>
          </div>

        </form>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">خروج</button>
      </div>

    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ⛔ أهم نقطة: نختار النافذة الصحيحة ID الجديد
    const modalElement = document.getElementById('cancelRequestSessionModal');
    const modal = new bootstrap.Modal(modalElement);

    const form = modalElement.querySelector('#cancel-request-session-form');
    const requestInput = modalElement.querySelector('#cancelRequestNumberInput');

    // ⭐ فتح النافذة من القائمة
    window.openCancelRequestModal = function () {
        modal.show();
        form.reset();

        // نفرغ جميع الحقول الخاصة بالعرض
        modalElement.querySelectorAll('td, span').forEach(el => el.textContent = '');
    };

    // ⭐ جلب التفاصيل عند كتابة رقم الطلب والضغط Enter
    requestInput.addEventListener('keypress', function (e) {
        if (e.key !== 'Enter') return;

        e.preventDefault();
        const requestNumber = requestInput.value.trim();
        if (!requestNumber) return;

        fetch(`/typist/cancel/${requestNumber}/details`)
            .then(response => {
                if (!response.ok) throw new Error('الطلب غير موجود');
                return response.json();
            })
            .then(data => {

                // تعبئة الحقول
                form.querySelector('[name="id"]').value = data.id ?? '';

                modalElement.querySelector('.tribunal-number-request').textContent   = data.tribunal_number ?? '';
                modalElement.querySelector('.department-number-request').textContent = data.department_number ?? '';
                modalElement.querySelector('.court-year-request').textContent        = data.court_year ?? '';

                modalElement.querySelector('.request-number-request').textContent = data.request_number ?? '';
                modalElement.querySelector('.title-request').textContent          = data.title ?? '';
                modalElement.querySelector('.plaintiff-request').textContent      = data.plaintiff ?? '';
                modalElement.querySelector('.defendant-request').textContent      = data.defendant ?? '';
                modalElement.querySelector('.third-party-request').textContent    = data.third_party ?? '';

                // ⭐ التاريخ الأصلي نعرضه من Created_at — backend لازم يرجعه الآن
                modalElement.querySelector('.original-date-request').textContent = data.original_date ?? '';

                modalElement.querySelector('.judge-name-request').textContent   = data.judge_name ?? '';

                modalElement.querySelector('.session-date-request').textContent = data.session_date ?? 'غير محدد';
                modalElement.querySelector('.session-time-request').textContent = data.session_time ?? 'غير محدد';

            })
            .catch(err => {
                console.error('فشل تحميل تفاصيل الطلب:', err);
                alert("❌ الطلب غير موجود");
            });
    });


    // ⭐ زر إلغاء الجلسة
    document.getElementById('cancel-session-request-button').addEventListener('click', function () {

        const requestId = form.querySelector('[name="id"]').value;
        if (!requestId) {
            alert("⚠️ الرجاء إدخال رقم الطلب أولاً");
            return;
        }

        if (!confirm("هل أنت متأكد من أنك تريد إلغاء الجلسة؟")) return;

        fetch(`{{ route('typist.request.cancel-session') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success || "تم إلغاء الجلسة بنجاح");

            // إفراغ بيانات الجلسة فقط
            modalElement.querySelector('.session-date-request').textContent = '';
            modalElement.querySelector('.session-time-request').textContent = '';
        })
        .catch(error => {
            console.error('خطأ في إلغاء الجلسة:', error);
            alert("⚠️ حدث خطأ أثناء الإلغاء");
        });

    });

});
</script>


<!-- نافذة أحكام الطلب -->
<!-- نافذة أحكام الطلب -->
<div class="modal fade" id="requestJudgmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">أحكام الطلب</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- بيانات رأس الصفحة -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>رقم المحكمة</label>
                        <input type="text" id="tribunal_number_j" class="form-control" readonly>
                    </div>

                    <div class="col-md-3">
                        <label>رقم القلم</label>
                        <input type="text" id="department_number_j" class="form-control" readonly>
                    </div>

                    <div class="col-md-3">
                        <label>السنة</label>
                        <input type="text" id="court_year_j" class="form-control" readonly>
                    </div>

                    <div class="col-md-3">
                        <label>رقم الطلب</label>
                        <input type="text" id="request_number_j" class="form-control" placeholder="أدخل رقم الطلب">
                    </div>
                </div>

                <!-- تاريخ الحكم + الإغلاق -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>تاريخ الحكم</label>
                        <input type="date" id="judgment_date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>تاريخ الإغلاق</label>
                        <input type="date" id="closure_date" class="form-control">
                    </div>
                </div>

                <hr>

                <!-- أزرار الاختيار -->
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-outline-primary" id="btn_against_parties">الحكم ضد الأطراف</button>
                    <button class="btn btn-outline-secondary" id="btn_final_judgment">الحكم الفاصل</button>
                    <button class="btn btn-outline-danger" id="btn_waiver">إسقاط الحق الشخصي</button>
                </div>

                <!-- المنطقة الديناميكية -->
                <div id="dynamic_area"></div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="save_judgment">💾 حفظ الحكم</button>
                <button class="btn btn-danger" data-bs-dismiss="modal">إغلاق</button>
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // فتح نافذة أحكام الطلب
    window.openRequestJudgmentModal = function () {
        let modal = new bootstrap.Modal(document.getElementById('requestJudgmentModal'));
        modal.show();

        // تنظيف البيانات المخزنة مؤقتاً
        window.currentParties = null;
        window.textAgainst = {};
        window.textFinal = "";
        window.textWaiver = "";
        document.getElementById("dynamic_area").innerHTML = "";
    };



    // -------------------------------------------
    //   جلب بيانات الطلب والأطراف
    // -------------------------------------------
    function fetchRequestData(requestNumber) {
        axios.get("{{ route('typist.judgment.open') }}", {
            params: { request_number: requestNumber }
        })
        .then(response => {

            let data = response.data.request;

            document.getElementById('tribunal_number_j').value = data.tribunal.number;
            document.getElementById('department_number_j').value = data.department.number;
            document.getElementById('court_year_j').value = data.court_year;

            window.currentRequestId = data.id;

            // تخزين الأطراف
            window.currentParties = {
                plaintiff: data.plaintiff_name,
                defendant: data.defendant_name,
                third_party: data.third_party_name,
                lawyer: data.lawyer_name
            };

        })
        .catch(() => {
            alert("❌ لم يتم العثور على طلب بهذا الرقم");
        });
    }

    // عند الضغط Enter داخل خانة رقم الطلب
    document.getElementById('request_number_j').addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            fetchRequestData(this.value.trim());
        }
    });




    // -------------------------------------------
    //  🔵 الحكم ضد الأطراف
    // -------------------------------------------
    document.getElementById('btn_against_parties').addEventListener('click', function () {

        if (!window.currentParties) {
            alert("⚠ يرجى إدخال رقم الطلب والضغط Enter أولاً");
            return;
        }

        let p = window.currentParties;

        let dropdown = '';
        if (p.plaintiff)    dropdown += `<option value="plaintiff">${p.plaintiff}</option>`;
        if (p.defendant)    dropdown += `<option value="defendant">${p.defendant}</option>`;
        if (p.third_party)  dropdown += `<option value="third_party">${p.third_party}</option>`;
        if (p.lawyer)       dropdown += `<option value="lawyer">${p.lawyer}</option>`;

        // استرجاع النص المحفوظ للطرف المُختار إن وجد
        let savedText = "";
        const selectedParty = Object.keys(window.textAgainst)[0];
        if (selectedParty) savedText = window.textAgainst[selectedParty];

        document.getElementById('dynamic_area').innerHTML = `
            <label>اختر الطرف</label>
            <select id="selected_party" class="form-control mb-3">
                ${dropdown}
            </select>

            <label>نص الحكم</label>
            <textarea id="judgment_text" class="form-control" rows="4">${savedText || ''}</textarea>
        `;

        // عند تغيير الطرف — نرجّع النص المخزن
        setTimeout(() => {
            document.getElementById("selected_party").addEventListener("change", function () {
                let key = this.value;
                document.getElementById("judgment_text").value = window.textAgainst[key] || "";
            });

            document.getElementById("judgment_text").addEventListener("input", function () {
                let key = document.getElementById("selected_party").value;
                window.textAgainst[key] = this.value;
            });
        }, 100);

    });




    // -------------------------------------------
    // 🔵 الحكم الفاصل
    // -------------------------------------------
    document.getElementById('btn_final_judgment').addEventListener('click', function () {

        document.getElementById('dynamic_area').innerHTML = `
            <label>نص الحكم الفاصل</label>
            <textarea id="judgment_text_final" class="form-control" rows="4">${window.textFinal || ''}</textarea>
        `;

        setTimeout(() => {
            document.getElementById("judgment_text_final").addEventListener("input", function () {
                window.textFinal = this.value;
            });
        }, 100);

    });




    // -------------------------------------------
    // 🔵 إسقاط الحق الشخصي
    // -------------------------------------------
    document.getElementById('btn_waiver').addEventListener('click', function () {

        document.getElementById('dynamic_area').innerHTML = `
            <label>نص إسقاط الحق الشخصي</label>
            <textarea id="judgment_text_waiver" class="form-control" rows="4">${window.textWaiver || ''}</textarea>
        `;

        setTimeout(() => {
            document.getElementById("judgment_text_waiver").addEventListener("input", function () {
                window.textWaiver = this.value;
            });
        }, 100);

    });




    // -------------------------------------------
    // 🔵 زر الحفظ النهائي
    // -------------------------------------------
    document.getElementById('save_judgment').addEventListener('click', function () {

        axios.post("{{ route('typist.judgment.store') }}", {
            request_id: window.currentRequestId,
            judgment_date: document.getElementById('judgment_date').value,
            closure_date: document.getElementById('closure_date').value,

            text_against: window.textAgainst,
            text_final: window.textFinal,
            text_waiver: window.textWaiver,
        })
        .then(() => {
            alert("✔ تم حفظ الحكم بالكامل");
        })
        .catch(err => {
            console.error(err);
            alert("❌ حدث خطأ أثناء حفظ الحكم");
        });

    });

});
</script>
@endsection








