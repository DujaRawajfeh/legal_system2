@extends('layouts.app')

@section('title', 'لوحة الكاتب')

@section('content')

<!-- ⭐ قائمة الدعوى / الطلب الخاصة بالكاتب -->
<div id="writer-case-options"
     style="
        display:none;
        position:absolute;
        background:#fff;
        border:1px solid #ccc;
        width:250px;
        z-index:999999999;
        text-align:right;
        box-shadow:0 4px 8px rgba(0,0,0,0.18);
     ">
    <ul style="list-style:none; margin:0; padding:0;">
        <li id="open-register-case"
    style="padding:10px; border-bottom:1px solid #ddd; cursor:pointer;">
    تسجيل دعوى
</li>


<li id="open-register-request"
    style="padding:10px; border-bottom:1px solid #ddd; cursor:pointer;">
    تسجيل طلب
</li>
        <li style="padding:10px; border-bottom:1px solid #ddd; cursor:pointer;"
            data-bs-toggle="modal" data-bs-target="#withdrawCaseModal">
            سحب دعوى / المدعي العام
        </li>

        <li style="padding:10px; border-bottom:1px solid #ddd; cursor:pointer;"
            data-bs-toggle="modal" data-bs-target="#pullPoliceCaseModal">
            سحب قضية من الشرطة
        </li>

        <li style="padding:10px; cursor:pointer;"
            data-bs-toggle="modal" data-bs-target="#participantsModal">
            المشاركين
        </li>
    </ul>
</div>
<!--نافذه تسجيل دعوى -->
<div class="modal fade" id="registerCaseModal" tabindex="-1" aria-labelledby="registerCaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="margin-top: 80px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerCaseLabel">تسجيل الدعوى</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <div class="modal-body">
        <form class="row g-3" method="POST" action="/writer/store-case">
  @csrf

          <!-- ✅ نوع الدعوى -->
          <div class="col-md-3">
            <label class="form-label">نوع الدعوى</label>
            <select class="form-select form-select-sm" id="caseType">
              <option value="">اختر نوع القضية...</option>
              <option value="القتل العمد">القتل العمد</option>
              <option value="القتل العمد مع سبق الإصرار">القتل العمد مع سبق الإصرار</option>
              <option value="القتل الخطأ">القتل الخطأ</option>
              <option value="السرقة">السرقة</option>
              <option value="الاغتصاب">الاغتصاب</option>
              <option value="الاعتداء الجسدي">الاعتداء الجسدي</option>
              <option value="المخدرات - تعاطي">المخدرات - تعاطي</option>
              <option value="المخدرات - اتجار">المخدرات - اتجار</option>
              <option value="المخدرات - ترويج">المخدرات - ترويج</option>
              <option value="الخطف">الخطف</option>
              <option value="الجرائم الإلكترونية">الجرائم الإلكترونية</option>
              <option value="الجرائم ضد أمن الدولة">الجرائم ضد أمن الدولة</option>
            </select>
          </div>

          <!-- ✅ رقم المحكمة -->
          <div class="col-md-3">
            <label class="form-label">رقم المحكمة</label>
            <input type="text" class="form-control form-control-sm" id="courtNumber" value="{{ auth()->user()->tribunal->number }}" readonly>
          </div>

          <!-- ✅ رقم القلم -->
          <div class="col-md-3">
            <label class="form-label">رقم القلم</label>
            <input type="text" class="form-control form-control-sm" id="departmentNumber" value="{{ auth()->user()->department->number }}" readonly>
          </div>

          <!-- ✅ رقم الدعوى -->
          <div class="col-md-3">
            <label class="form-label">رقم الدعوى</label>
            <input type="text" class="form-control form-control-sm" id="caseNumber" placeholder="اضغط Enter">
          </div>

          <!-- ✅ السنة -->
          <div class="col-md-3">
            <label class="form-label">السنة</label>
            <input type="text" class="form-control form-control-sm" id="caseYear" placeholder="اضغط Enter">
          </div>

          <!-- ✅ نوع الطرف -->
          <div class="col-md-3">
            <label class="form-label">نوع الطرف</label>
            <select class="form-select form-select-sm" id="partyType">
              <option value="">اختر...</option>
              <option value="مشتكي">مشتكي</option>
              <option value="مشتكى عليه">مشتكى عليه</option>
              <option value="شاهد">شاهد</option>
            </select>
          </div>

          <!-- ✅ اسم الطرف -->
          <div class="col-md-3">
            <label class="form-label">اسم الطرف</label>
            <input type="text" class="form-control form-control-sm" id="partyName">
          </div>

          <!-- ✅ الرقم الوطني -->
          <div class="col-md-3">
            <label class="form-label">الرقم الوطني</label>
            <input type="text" class="form-control form-control-sm" id="nationalId">
          </div>

          <!-- ✅ مكان السكن -->
          <div class="col-md-3">
            <label class="form-label">مكان السكن</label>
            <input type="text" class="form-control form-control-sm" id="residence">
          </div>

          <!-- ✅ الوظيفة / مكان العمل -->
          <div class="col-md-3">
            <label class="form-label">الوظيفة / مكان العمل</label>
            <input type="text" class="form-control form-control-sm" id="job">
          </div>

          <!-- ✅ رقم الهاتف -->
          <div class="col-md-3">
            <label class="form-label">رقم الهاتف</label>
            <input type="text" class="form-control form-control-sm" id="phone">
          </div>
          

        <!-- التهمة -->
<div class="col-md-3">
    <label class="form-label">التهمة</label>
    <input type="text" class="form-control form-control-sm" id="charge" placeholder="أدخل التهمة">
</div>


          <div class="mb-3">
  <label class="form-label">القاضي المعيّن تلقائيًا</label>
  <input type="text" id="judge_name" class="form-control" readonly>
  <input type="hidden" name="judge_id" id="judge_id">
</div>
<div class="mb-3">
  <label class="form-label">موعد الجلسة</label>
  <input type="text" id="session_date" class="form-control" readonly>

</div>
        </form>
      </div>
      <!-- ✅ أزرار التحكم -->
      <div class="modal-footer d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-success btn-sm" id="saveParty">حفظ</button>
          <button type="submit" class="btn btn-primary btn-sm" id="saveAndFinish">حفظ وإنهاء</button>
        </div>
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">إنهاء المدخلات</button>
      </div>
    </div>
  </div>
</div>




<!--  نافذة تسجيل طلب -->
<!--  نافذة تسجيل طلب -->
<div class="modal fade" id="registerRequestModal" tabindex="-1" aria-labelledby="registerRequestLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="margin-top:80px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">تسجيل طلب</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="requestForm" class="row g-3">

          <!-- رقم المحكمة -->
          <div class="col-md-3">
            <label class="form-label">رقم المحكمة</label>
            <input type="text" class="form-control form-control-sm" id="reqCourtNumber" readonly>
          </div>

          <!-- رقم القلم -->
          <div class="col-md-3">
            <label class="form-label">رقم القلم</label>
            <input type="text" class="form-control form-control-sm" id="reqDepartmentNumber" readonly>
          </div>

          <!-- السنة -->
          <div class="col-md-3">
            <label class="form-label">السنة</label>
            <input type="text" class="form-control form-control-sm" id="reqYear" readonly>
          </div>

          <!-- رقم الطلب -->
          <div class="col-md-3">
            <label class="form-label">رقم الطلب</label>
            <input type="text" class="form-control form-control-sm" id="requestNumber" placeholder="اضغط Enter">
          </div>

          <!-- نوع الطلب -->
          <div class="col-md-4">
            <label class="form-label">نوع الطلب</label>
            <select class="form-select form-select-sm" id="requestType">
              <option value="">اختر نوع الطلب...</option>
              <option value="طلب تنفيذ">طلب تنفيذ</option>
              <option value="طلب إثبات حالة">طلب إثبات حالة</option>
              <option value="طلب مستعجل">طلب مستعجل</option>
              <option value="طلب تعليق">طلب تعليق</option>
            </select>
          </div>

          <!-- 🟦 الطرف الأول (ظاهر افتراضياً) -->
          <div class="request-party row g-3 mt-2">

              <div class="col-md-4">
                  <label class="form-label">نوع الطرف</label>
                  <select class="form-select form-select-sm request-party-type">
                    <option value="">اختر...</option>
                    <option value="مشتكي">مشتكي</option>
                    <option value="مشتكى عليه">مشتكى عليه</option>
                    <option value="شاهد">شاهد</option>
                    <option value="محامي">محامي</option>
                  </select>
              </div>

              <div class="col-md-4">
                  <label class="form-label">اسم الطرف</label>
                  <input type="text" class="form-control form-control-sm request-party-name">
              </div>

              <div class="col-md-4">
                  <label class="form-label">الرقم الوطني</label>
                  <input type="text" class="form-control form-control-sm request-party-nid">
              </div>

              <div class="col-md-4">
                  <label class="form-label">مكان السكن</label>
                  <input type="text" class="form-control form-control-sm request-party-residence">
              </div>

              <div class="col-md-4">
                  <label class="form-label">الوظيفة / مكان العمل</label>
                  <input type="text" class="form-control form-control-sm request-party-job">
              </div>

              <div class="col-md-4">
                  <label class="form-label">رقم الهاتف</label>
                  <input type="text" class="form-control form-control-sm request-party-phone">
              </div>

          </div>

          <!-- 🟨 قالب طرف مخفي (يستخدم للنسخ فقط) -->
          <div id="partyTemplate" class="request-party d-none row g-3 mt-2">

              <div class="col-md-4">
                  <select class="form-select form-select-sm request-party-type">
                    <option value="">اختر...</option>
                    <option value="مشتكي">مشتكي</option>
                    <option value="مشتكى عليه">مشتكى عليه</option>
                    <option value="شاهد">شاهد</option>
                    <option value="محامي">محامي</option>
                  </select>
              </div>

              <div class="col-md-4">
                  <input type="text" class="form-control form-control-sm request-party-name" placeholder="اسم الطرف">
              </div>

              <div class="col-md-4">
                  <input type="text" class="form-control form-control-sm request-party-nid" placeholder="الرقم الوطني">
              </div>

              <div class="col-md-4">
                  <input type="text" class="form-control form-control-sm request-party-residence" placeholder="مكان السكن">
              </div>

              <div class="col-md-4">
                  <input type="text" class="form-control form-control-sm request-party-job" placeholder="الوظيفة / مكان العمل">
              </div>

              <div class="col-md-3">
                  <input type="text" class="form-control form-control-sm request-party-phone" placeholder="رقم الهاتف">
              </div>

              <div class="col-md-1">
                  <button type="button" class="btn btn-danger btn-sm remove-party">X</button>
              </div>

          </div>

          <!-- زر إضافة طرف -->
          <div class="col-12">
            <button type="button" id="addRequestParty" class="btn btn-secondary btn-sm">
              ➕ إضافة طرف آخر
            </button>
          </div>

          <!-- حاوية الأطراف الإضافية -->
          <div id="extraRequestParties"></div>

          <hr>

          <!-- القاضي -->
          <div class="col-md-6">
            <label class="form-label">القاضي المعيّن تلقائيًا</label>
            <input type="text" id="reqJudgeName" class="form-control" readonly>
            <input type="hidden" id="reqJudgeId">
          </div>

          <!-- موعد الجلسة -->
          <div class="col-md-6">
            <label class="form-label">موعد الجلسة</label>
            <input type="text" id="reqSessionDate" class="form-control" readonly>
          </div>

        </form>

      </div>

      <div class="modal-footer">
        <!-- ✳ مهم جداً: type="button" عشان ما يعمل submit للفورم -->
        <button type="button" class="btn btn-primary btn-sm" id="saveRequest">
          حفظ الطلب
        </button>
      </div>

    </div>
  </div>
</div>


<!-- قائمة محاضر الجلسات الخاصة بالكاتب -->
<ul id="writer-sessions-submenu"
    style="display:none; position:absolute;
           background:white; border:1px solid #ccc;
           padding:10px; min-width:180px;
           z-index:999999;">
    
    <li class="dropdown-item text-primary" onclick="openReportsListModal()">
         محاضر الجلسات
    </li>

    <!-- ✅ خيار جديد لفتح نافذة جدول الطلبات -->
    <li class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#requestScheduleModal">
         جدول الطلبات
    </li>

</ul>



</ul>
<div class="modal fade" id="reportsListModal" tabindex="-1">
  <div class="modal-dialog modal-lg" style="margin-top:80px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">محاضر الجلسات</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="reportsContainer">
            <p class="text-center text-secondary">جاري التحميل...</p>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- نافذة المشاركين / البحث في الأحوال المدنية -->
<div class="modal fade" id="participantsModal" tabindex="-1" aria-labelledby="participantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">البحث في الأحوال المدنية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- نموذج البحث -->
                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">الاسم الأول</label>
                        <input type="text" id="first_name" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اسم الأب</label>
                        <input type="text" id="father_name" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اسم الأم</label>
                        <input type="text" id="mother_name" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اسم الجد</label>
                        <input type="text" id="grandfather_name" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اسم العائلة</label>
                        <input type="text" id="family_name" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الوظيفة</label>
                        <input type="text" id="occupation" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الجنسية</label>
                        <input type="text" id="nationality" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الجنس</label>
                        <select id="gender" class="form-select form-select-sm">
                            <option value="">اختر...</option>
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                    </div>

                </div>

                <div class="mt-3 text-end">
                    <button class="btn btn-primary btn-sm" onclick="searchCivilRegistry()">بحث الأحوال المدنية</button>
                </div>

                <hr>

                <!-- جدول عرض نتائج السجل المدني -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>الرقم الوطني</th>
                                <th>الاسم الأول</th>  <!-- ⭐ فقط first_name -->
                                <th>اسم الأب</th>
                                <th>اسم الأم</th>
                                <th>اسم الجد</th>
                                <th>اسم العائلة</th>
                                <th>تاريخ الميلاد</th>
                                <th>العمر</th>
                                <th>الجنس</th>
                                <th>الديانة</th>
                                <th>الجنسية</th>
                                <th>مكان الولادة</th>
                                <th>الوظيفة</th>
                                <th>التعليم</th>
                                <th>رقم الهاتف</th>
                                <th>مكان السجل</th>
                            </tr>
                        </thead>
                        <tbody id="civilResults">
                            <!-- يتم تعبئته من JS -->
                        </tbody>
                    </table>
                </div>

            </div> <!-- /modal-body -->

        </div>
    </div>
</div>


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
















@yield('chief-extra')
@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {

    function fetchRequestSchedule() {
        const requestNumber = document.getElementById('requestNumberInput').value;

        if (!requestNumber) {
            alert('يرجى إدخال رقم الطلب');
            return;
        }

        // ✅ تعديل المسار ليكون خاص بالكاتب
        fetch('/writer/request-schedule', {
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

                    // ✅ عرض معلومات المحكمة
                    console.log("📥 Full record:", first);
                    console.log("🔑 Keys:", Object.keys(first));
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

    // ✅ ربط الدالة بزر الإدخال إذا ضغط Enter
    const input = document.getElementById('requestNumberInput');
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            fetchRequestSchedule();
        }
    });

});
</script>





















<script>
    //نافذه المشاركين
document.addEventListener("DOMContentLoaded", function () {

    console.log("📌 participants JS Loaded");

    window.searchCivilRegistry = function () {

        console.log("📌 Starting Civil Registry Search...");

        const params = {
            first_name: document.getElementById("first_name").value.trim(),
            father_name: document.getElementById("father_name").value.trim(),
            mother_name: document.getElementById("mother_name").value.trim(),
            grandfather_name: document.getElementById("grandfather_name").value.trim(),
            family_name: document.getElementById("family_name").value.trim(),
            occupation: document.getElementById("occupation").value.trim(),
            nationality: document.getElementById("nationality").value.trim(),
            gender: document.getElementById("gender").value.trim(),
        };

        fetch("/civil-registry/search", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(params)
        })
        .then(res => res.json())
        .then(data => {
            console.log("📥 Civil Registry Results:", data);

            // ✅ خزّن البيانات في متغير عام للوصول من الـ Console
            window.civilData = data;

            // اطبع المفاتيح والقيمة للتأكد
            if (Array.isArray(data) && data.length > 0) {
                console.log("🔑 Keys:", Object.keys(data[0]));
                console.log("🟢 First Name Value:", data[0].first_name);
            }

            const tbody = document.getElementById("civilResults");
            tbody.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="16" class="text-center text-muted">لا توجد نتائج</td></tr>`;
                return;
            }

            data.forEach(item => {
                const firstName = item["first_name"] ? String(item["first_name"]).trim() : '-';
                tbody.innerHTML += "<tr>"
                    + "<td>" + (item["national_id"] ?? '-') + "</td>"
                    + "<td style='direction: rtl; unicode-bidi: embed;'>" + firstName + "</td>"
                    + "<td>" + (item["father_name"] ?? '-') + "</td>"
                    + "<td>" + (item["mother_name"] ?? '-') + "</td>"
                    + "<td>" + (item["grandfather_name"] ?? '-') + "</td>"
                    + "<td>" + (item["family_name"] ?? '-') + "</td>"
                    + "<td>" + (item["birth_date"] ?? '-') + "</td>"
                    + "<td>" + (item["age"] ?? '-') + "</td>"
                    + "<td>" + (item["gender"] ?? '-') + "</td>"
                    + "<td>" + (item["religion"] ?? '-') + "</td>"
                    + "<td>" + (item["nationality"] ?? '-') + "</td>"
                    + "<td>" + (item["place_of_birth"] ?? '-') + "</td>"
                    + "<td>" + (item["occupation"] ?? '-') + "</td>"
                    + "<td>" + (item["education_level"] ?? '-') + "</td>"
                    + "<td>" + (item["phone_number"] ?? '-') + "</td>"
                    + "<td>" + (item["record_location"] ?? '-') + "</td>"
                    + "</tr>";
            });

        })
        .catch(err => {
            console.error("❌ Error:", err);
            alert("حدث خطأ أثناء البحث");
        });
    };

});
</script>




@push('scripts')
<!-- قائمة محاضر الجلسات الخاصة بالكاتب -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const trigger = document.getElementById('sessions-trigger'); // من layouts.app
    const menu = document.getElementById('writer-sessions-submenu');

    if (!trigger || !menu) return;

    // عند الوقوف على كلمة الجلسات
    trigger.addEventListener('mouseenter', () => {
        menu.style.display = 'block';
    });

    // اخفاء القائمة عند خروج الماوس
    trigger.addEventListener('mouseleave', () => {
        setTimeout(() => {
            if (!menu.matches(':hover')) {
                menu.style.display = 'none';
            }
        }, 150);
    });

    menu.addEventListener('mouseleave', () => {
        menu.style.display = 'none';
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const trigger = document.getElementById('sessions-trigger'); // من layouts.app
    const menu = document.getElementById('writer-sessions-submenu');

    if (!trigger || !menu) return;

    // ⭐ إظهار القائمة تحت كلمة "الجلسات"
    trigger.addEventListener('mouseenter', () => {

        // موقع كلمة الجلسات على الشاشة
        const rect = trigger.getBoundingClientRect();

        // حساب المكان بدقة تحت الكلمة
        menu.style.top  = (rect.bottom + window.scrollY) + "px";     // تحت الكلمة مباشرة
        menu.style.right = (window.innerWidth - rect.right) + "px";  // محاذاة يمين الكلمة

        menu.style.display = "block";
    });

    // ⭐ إخفاء القائمة عندما يخرج الماوس
    trigger.addEventListener('mouseleave', () => {
        setTimeout(() => {
            if (!menu.matches(':hover')) {
                menu.style.display = "none";
            }
        }, 150);
    });

    menu.addEventListener('mouseleave', () => {
        menu.style.display = "none";
    });

});
</script>
<script>
window.openReportsListModal = function () {
    const modal = new bootstrap.Modal(document.getElementById('reportsListModal'));
    modal.show();
    loadReportsList();
}

async function loadReportsList() {

    const container = document.getElementById("reportsContainer");
    container.innerHTML = `<p class="text-center text-secondary">جاري التحميل...</p>`;

    try {
        // ✅ نستخدم الراوت بالاسم
        const response = await axios.get("{{ route('writer.reports.list') }}");
        const data = response.data.reports || [];

        if (data.length === 0) {
            container.innerHTML = `<p class="text-danger text-center">لا يوجد محاضر</p>`;
            return;
        }

        // ✅ قوالب الروابط مع PLACEHOLDER
        const trialUrlTemplate = `{{ route('writer.trial.report.show', ['session' => 'SESSION_ID']) }}`;
        const afterUrlTemplate = `{{ route('writer.after-trial.report.show', ['session' => 'SESSION_ID']) }}`;

        let html = "";

        data.forEach(row => {

            const c = row.case;          // { id, number, type }
            const modes = row.modes || [];
            const sessionId = row.session_id;

            // ✅ نبدل SESSION_ID بالـ sessionId الحقيقي
            const trialUrl = trialUrlTemplate.replace('SESSION_ID', sessionId);
            const afterUrl = afterUrlTemplate.replace('SESSION_ID', sessionId);

            html += `
                <div class="border rounded p-3 mb-2">
                    <h6>قضية: ${c.number} — ${c.type}</h6>

                    <div class="mt-2 d-flex gap-2">
                        ${modes.includes('trial')
                            ? `<a class="btn btn-primary btn-sm" href="${trialUrl}" target="_blank">محضر المحاكمة</a>`
                            : ''}

                        ${modes.includes('after')
                            ? `<a class="btn btn-secondary btn-sm" href="${afterUrl}" target="_blank">محضر ما بعد</a>`
                            : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

    } catch (e) {
        console.error('❌ خطأ أثناء تحميل المحاضر:', e);
        container.innerHTML = `<p class="text-danger text-center">فشل التحميل</p>`;
    }
}
</script>
















<script>
document.addEventListener("DOMContentLoaded", () => {

    console.log("📌 JS تسجيل الطلب يعمل...");

    let currentRequestId = null;

    // فتح نافذة تسجيل الطلب
    const openBtn = document.getElementById("open-register-request");
    const modalEl = document.getElementById("registerRequestModal");

    if (openBtn && modalEl) {
        openBtn.addEventListener("click", () => {

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            document.getElementById("reqCourtNumber").value = "{{ auth()->user()->tribunal->number }}";
            document.getElementById("reqDepartmentNumber").value = "{{ auth()->user()->department->number }}";
            document.getElementById("reqYear").value = new Date().getFullYear();

            currentRequestId = null;
        });
    }

    // ⭐ توليد رقم الطلب
    document.getElementById("requestNumber").addEventListener("keydown", async (e) => {

        if (e.key !== "Enter") return;
        e.preventDefault();

        const type = document.getElementById("requestType").value;

        if (!type) {
            alert("❌ اختر نوع الطلب أولاً");
            return;
        }

        try {
            const response = await axios.post("/writer/request/store-number", { type });
            const d = response.data;

            currentRequestId = d.id;

            document.getElementById("requestNumber").value = d.request_number;
            document.getElementById("reqJudgeName").value  = d.judge_name;
            document.getElementById("reqJudgeId").value    = d.judge_id;
            document.getElementById("reqSessionDate").value = d.session_date;

        } catch (err) {
            console.error(err);
        }
    });

    // ⭐ إضافة طرف
    const addPartyBtn = document.getElementById("addRequestParty");
    const partyTemplate = document.getElementById("partyTemplate");
    const partyContainer = document.getElementById("extraRequestParties");

    addPartyBtn.addEventListener("click", () => {

        let clone = partyTemplate.cloneNode(true);
        clone.classList.remove("d-none");
        clone.removeAttribute("id");

        clone.querySelectorAll("input").forEach(el => el.value = "");
        clone.querySelectorAll("select").forEach(el => el.value = "");

        clone.querySelector(".remove-party").addEventListener("click", () => {
            clone.remove();
        });

        partyContainer.appendChild(clone);
    });

    // ⭐ حفظ الطلب بالكامل
    document.getElementById("saveRequest").addEventListener("click", async () => {

        if (!currentRequestId) {
            alert("❌ اضغط Enter لتوليد رقم الطلب أولاً");
            return;
        }

        let parties = [];

        // الطرف الأول + الأطراف المضافة
        document.querySelectorAll(".request-party:not(#partyTemplate)").forEach(block => {

            const p = {
                type:       block.querySelector(".request-party-type").value,
                name:       block.querySelector(".request-party-name").value,
                national_id:block.querySelector(".request-party-nid").value,
                residence:  block.querySelector(".request-party-residence").value,
                job:        block.querySelector(".request-party-job").value,
                phone:      block.querySelector(".request-party-phone").value,
            };

            if (p.type && p.name) {
                parties.push(p);
            }
        });

        if (parties.length === 0) {
            alert("❌ يجب إدخال طرف واحد على الأقل");
            return;
        }

        try {

            const res = await axios.post("/requests/store-parties", {
                request_id: currentRequestId,
                parties: parties
            });

            alert("✔ تم حفظ الطلب بنجاح");

        } catch (err) {
            console.error(err);
            alert("❌ خطأ أثناء حفظ الأطراف");
        }

    });

});
</script>












<script>
document.addEventListener('DOMContentLoaded', function () {

    console.log("🔥 participants JS Loaded");

    /* ============================================================
       🔍 وظيفة البحث بالأحوال المدنية
    ============================================================ */
    window.searchCivilRegistry = function () {

        const payload = {
            first_name:     document.getElementById("first_name").value,
            father_name:    document.getElementById("father_name").value,
            mother_name:    document.getElementById("mother_name").value,
            grandfather_name: document.getElementById("grandfather_name").value,
            family_name:    document.getElementById("family_name").value,
            gender:         document.getElementById("gender").value,
            occupation:     document.getElementById("occupation").value,
            nationality:    document.getElementById("nationality").value,
            // ✅ حذفنا birth_date من الـ payload
        };

        console.log("📤 Sending request → /civil-registry/search", payload);

        fetch("/civil-registry/search", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {

            console.log("📥 Civil Registry Results:", data);

            const tbody = document.getElementById("civilResults");
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                // ✅ عدد الأعمدة = 16 الآن (بدون عمود اختيار)
                tbody.innerHTML = `<tr><td colspan="16" class="text-danger text-center">لا توجد نتائج</td></tr>`;
                return;
            }

            data.forEach(item => {

                // ✅ تنسيق تاريخ الميلاد (قص أول 10 خانات فقط)
                let birthDate = '-';
                if (item.birth_date) {
                    birthDate = item.birth_date.toString().substring(0, 10);
                }

                tbody.innerHTML += `
                    <tr>
                        <td>${item.national_id       ?? '-'}</td>
                        <td>${item.full_name         ?? '-'}</td>
                        <td>${item.father_name       ?? '-'}</td>
                        <td>${item.mother_name       ?? '-'}</td>
                        <td>${item.grandfather_name  ?? '-'}</td>
                        <td>${item.family_name       ?? '-'}</td>
                        <td>${birthDate}</td>
                        <td>${item.age               ?? '-'}</td>
                        <td>${item.gender            ?? '-'}</td>
                        <td>${item.religion          ?? '-'}</td>
                        <td>${item.nationality       ?? '-'}</td>
                        <td>${item.place_of_birth    ?? '-'}</td>
                        <td>${item.occupation        ?? '-'}</td>
                        <td>${item.education_level   ?? '-'}</td>
                        <td>${item.phone_number      ?? '-'}</td>
                        <td>${item.record_location   ?? '-'}</td>
                        <!-- ✅ حذفنا عمود اختيار -->
                    </tr>
                `;
            });

        })
        .catch(err => {
            console.error("❌ Civil Registry Error:", err);
            alert("حدث خطأ أثناء البحث في السجل المدني");
        });

    }; // END searchCivilRegistry



    /* ============================================================
       🟦 دالة selectCivil (موجودة احتياطًا لو احتجتيها لاحقًا)
       — حاليًا ما في زر اختيار في الجدول
    ============================================================ */
    window.selectCivil = function (item) {

        console.log("✔ Selected Civil Person:", item);

        if (document.getElementById("partyName")) {
            document.getElementById("partyName").value =
                `${item.first_name ?? ''} ${item.father_name ?? ''} ${item.grandfather_name ?? ''} ${item.family_name ?? ''}`;
        }

        if (document.getElementById("nationalId"))
            document.getElementById("nationalId").value = item.national_id ?? '';

        if (document.getElementById("residence"))
            document.getElementById("residence").value = item.current_address ?? '';

        if (document.getElementById("job"))
            document.getElementById("job").value = item.occupation ?? '';

        if (document.getElementById("phone"))
            document.getElementById("phone").value = item.phone_number ?? '';

        alert("✔ تم اختيار الشخص من السجل المدني");

        const modalEl = document.getElementById('participantsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    };

}); // END DOMContentLoaded
</script>
<script>
  //إظهار القائمة الدعوى/الطلب
document.addEventListener('DOMContentLoaded', function () {

    const menu = document.getElementById('writer-case-options');
    const trigger = document.getElementById('trigger-cases'); // من layouts.app

    console.log("📌 Writer page loaded");

    if (!menu) console.warn("⚠️ القائمة غير موجودة!");
    if (!trigger) console.warn("⚠️ trigger-cases غير موجود في الصفحة!");

    if (!menu || !trigger) return;

    /* 📌 إظهار القائمة عندما يرسل layouts.event الإشارة */
    document.addEventListener('showWriterCasesMenu', () => {

        const rect = trigger.getBoundingClientRect();

        // ⭐ وضع القائمة تحت الكلمة في RTL
        menu.style.top = rect.bottom + window.scrollY + "px";
        menu.style.right = (window.innerWidth - rect.right) + "px";

        menu.style.display = "block";
        console.log("📌 القائمة ظهرت الآن");
    });

    /* 📌 إخفاء القائمة */
    document.addEventListener('hideWriterCasesMenu', () => {

        setTimeout(() => {
            if (!menu.matches(':hover') && !trigger.matches(':hover')) {
                menu.style.display = "none";
                console.log("📌 القائمة اختفت");
            }
        }, 150);
    });

    /* 📌 إخفاء عند خروج الماوس */
    menu.addEventListener('mouseleave', () => {
        if (!trigger.matches(':hover')) {
            menu.style.display = "none";
        }
    });

});
</script>
@endpush