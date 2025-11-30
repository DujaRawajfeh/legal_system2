<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام المحكمة')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        direction: rtl;
        background-color: #f9f9f9;
    }

    .top-bar {
        background-color: #004080;
        color: white;
        font-size: 14px;
        padding: 4px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .menu-bar {
        background-color: #e0e0e0;
        font-size: 13px;
        padding: 4px 12px;
        display: flex;
        gap: 20px;
        border-bottom: 1px solid #ccc;
        position: relative;
    }

    .third-bar {
        background-color: #f0f0f0;
        font-size: 13px;
        padding: 6px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ccc;
    }

    .third-bar input {
        width: 90px;
        font-size: 13px;
    }

/* 🔵 تصغير حجم دوائر الـ Radio (دعوى / طلب) */
.third-bar .form-check-input {
    width: 12px !important;
    height: 12px !important;
    margin-top: 2px;
    cursor: pointer;
}



    .content {
        padding: 20px;
    }

    #case-options {
        display: none;
        position: absolute;
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        width: 250px;
        z-index: 9999;
        text-align: right;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translate(12px, 10px);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    #case-options li:hover {
        background-color: #e9ecef;
    }
    </style>
    <style>
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 160px;
    border: 1px solid #ccc;
    z-index: 1000;
}

.dropdown-content a {
    color: black;
    padding: 8px 12px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}
</style>
</head>

<body>

{{-- 🔵 الشريط العلوي --}}
<div class="top-bar">
    <div>المحكمة: {{ optional(auth()->user()->tribunal)->name ?? '---' }}</div>
    <div>القلم: {{ optional(auth()->user()->department)->name ?? '---' }}</div>
    <div>الموظف: {{ auth()->user()->full_name ?? '---' }}</div>
</div>

{{-- 🔵 الشريط الثاني --}}
<div class="menu-bar">
    <span id="trigger-cases" style="cursor: pointer;">الدعوى / الطلب</span>
    <span id="trigger-notifications" style="cursor: pointer;">التباليغ</span>
    <span id="sessions-trigger" style="cursor: pointer;">الجلسات</span>
      <div class="dropdown">
        <span id="trigger-security" style="cursor: pointer;">الحماية ▾</span>
        <div id="security-menu" class="dropdown-content">
            <a href="{{ route('2fa.setup') }}">المصادقة الثنائية</a>
        </div>
    </div>
</div>
</div>

{{-- 🔵 الشريط الثالث --}}
<div class="third-bar">

    <div class="d-flex align-items-center">
        <label class="me-2 mb-0">النوع:</label>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="entry_type" id="type_case" value="case" checked>
            <label class="form-check-label" for="type_case">دعوى</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="entry_type" id="type_request" value="request">
            <label class="form-check-label" for="type_request">طلب</label>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <label class="mb-0">رقم الدعوى:</label>

        <input type="text" class="form-control form-control-sm" placeholder="المحكمة"
               readonly value="{{ optional(auth()->user()->tribunal)->number ?? '---' }}">

        <input type="text" class="form-control form-control-sm" placeholder="القلم"
               readonly value="{{ optional(auth()->user()->department)->number ?? '---' }}">

        {{-- ⭐ هذا هو الحقل الذي سنقرأ منه رقم الطلب --}}
        <input id="entryNumberInput" type="text" class="form-control form-control-sm" placeholder="الرقم">

        <input type="text" class="form-control form-control-sm" placeholder="السنة" readonly value="{{ date('Y') }}">
    </div>
</div>

{{-- ⭐⭐⭐ نافذة تفاصيل الطلب ⭐⭐⭐ --}}
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">تفاصيل الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="requestDetailsBody">
                <p class="text-center text-secondary">جاري التحميل...</p>
            </div>

        </div>
    </div>
</div>
<!-- ⭐⭐⭐ نافذة عرض تفاصيل الدعوى ⭐⭐⭐ -->
<div class="modal fade" id="caseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">تفاصيل الدعوى</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="caseDetailsBody">
                <p class="text-center text-secondary">جاري التحميل...</p>
            </div>

        </div>
    </div>
</div>




{{-- المحتوى --}}
<div class="content">
    @yield('content')
</div>

{{-- 🔵 سكربتات القائمة --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggerCases = document.getElementById('trigger-cases');

    triggerCases.addEventListener('mouseenter', () => {
        document.dispatchEvent(new Event('showWriterCasesMenu'));
    });

    triggerCases.addEventListener('mouseleave', () => {
        document.dispatchEvent(new Event('hideWriterCasesMenu'));
    });
});
</script>

{{-- 🔵 سكربت فتح نافذة الطلب --}}
<script>
// =============================
//  الضغط على Enter لفتح نافذة الطلب
// =============================
document.addEventListener('DOMContentLoaded', function () {

    const entryTypeRequest = document.getElementById("type_request");
    const entryInput = document.getElementById("entryNumberInput");

    entryInput.addEventListener("keydown", function (e) {

        if (e.key === "Enter" && entryTypeRequest.checked) {

            const reqNumber = entryInput.value.trim();
            if (!reqNumber) {
                alert("الرجاء إدخال رقم الطلب");
                return;
            }

            openRequestDetails(reqNumber);
        }
    });

});


// =============================
//   فتح نافذة تفاصيل الطلب
// =============================
function openRequestDetails(requestNumber) {

    const modal = new bootstrap.Modal(document.getElementById("requestDetailsModal"));
    const body  = document.getElementById("requestDetailsBody");

    body.innerHTML = `<p class="text-center text-secondary">جاري التحميل...</p>`;
    modal.show();

    loadRequestDetails(requestNumber);
}


// =============================
//   جلب بيانات الطلب (POST)
// =============================
async function loadRequestDetails(requestNumber) {

    const body = document.getElementById("requestDetailsBody");

    try {
        const response = await axios.post("{{ route('chief.request.details') }}", {
            request_number: requestNumber
        });

        if (!response.data.success) {
            body.innerHTML = `<p class="text-danger text-center">⚠️ ${response.data.message}</p>`;
            return;
        }

        const r = response.data.request;

        body.innerHTML = `
            <table class="table table-bordered">

                <tr><th>رقم الطلب</th><td>${r.request_number}</td></tr>
                <tr><th>عنوان الطلب</th><td>${r.title ?? '-'}</td></tr>
                <tr><th>التاريخ الأصلي</th><td>${r.original_date ?? '-'}</td></tr>

                <tr><th>تاريخ الجلسة</th><td>${r.session_date ?? '-'}</td></tr>
                <tr><th>وقت الجلسة</th><td>${r.session_time ?? '-'}</td></tr>

                <tr><th>غرض الجلسة</th><td>${r.session_purpose ?? '-'}</td></tr>
                <tr><th>سبب الجلسة</th><td>${r.session_reason ?? '-'}</td></tr>

                <tr><th>القاضي</th><td>${r.judge_name ?? '-'}</td></tr>

            </table>

            <h6 class="mt-4">الأطراف</h6>

            <table class="table table-bordered">
                <tr><th>الصفة</th><th>الاسم</th></tr>

                ${r.plaintiff_name ? `<tr><td>مشتكي</td><td>${r.plaintiff_name}</td></tr>` : ''}
                ${r.defendant_name ? `<tr><td>مشتكى عليه</td><td>${r.defendant_name}</td></tr>` : ''}
                ${r.third_party_name ? `<tr><td>طرف ثالث</td><td>${r.third_party_name}</td></tr>` : ''}
                ${r.lawyer_name ? `<tr><td>محامي</td><td>${r.lawyer_name}</td></tr>` : ''}
            </table>
        `;

    } catch (error) {

        const msg = error.response?.data?.message ?? "خطأ غير معروف";

        body.innerHTML = `
            <p class="text-danger text-center">❌ خطأ أثناء تحميل البيانات — ${msg}</p>
        `;
    }
}
</script>













<script>
    //رقم الدعوى الشريط الثالث
// =============================
// استماع لزر Enter عند اختيار "دعوى"
// =============================
document.addEventListener("DOMContentLoaded", function () {

    const entryTypeCase = document.getElementById("type_case");
    const inputs = document.querySelectorAll('.third-bar input[type="text"]');
    const caseNumberInput = inputs[2]; // رقم الدعوى

    caseNumberInput.addEventListener("keydown", function (e) {

        if (e.key === "Enter" && entryTypeCase.checked) {

            const caseNum = caseNumberInput.value.trim();

            if (!caseNum) {
                alert("الرجاء إدخال رقم الدعوى");
                return;
            }

            openCaseDetails(caseNum);
        }

    });

});


// =============================
//  فتح نافذة تفاصيل الدعوى
// =============================
function openCaseDetails(caseNumber) {

    const modal = new bootstrap.Modal(document.getElementById("caseDetailsModal"));
    const body  = document.getElementById("caseDetailsBody");

    body.innerHTML = `<p class="text-center text-secondary">جاري التحميل...</p>`;
    modal.show();

    loadCaseDetails(caseNumber);
}


// =============================
//  جلب بيانات الدعوى من السيرفر
// =============================
async function loadCaseDetails(caseNumber) {

    const body = document.getElementById("caseDetailsBody");

    try {
        const response = await axios.post("{{ route('chief.case.details') }}", {
            case_number: caseNumber
        });

        if (!response.data.success) {
            body.innerHTML = `<p class="text-danger text-center">${response.data.message}</p>`;
            return;
        }

        const c = response.data.case;

        let participantsHTML = "";
        c.participants.forEach(p => {
            participantsHTML += `
                <tr>
                    <td>${p.type}</td>
                    <td>${p.name}</td>
                    <td>${p.charge ?? '-'}</td>
                </tr>`;
        });

        let sessionsHTML = "";
        c.sessions.forEach(s => {
            sessionsHTML += `
                <tr>
                    <td>${s.id}</td>
                    <td>${s.time ?? '-'}</td>
                    <td>${s.date ?? '-'}</td>
                    <td>${s.reason ?? '-'}</td>
                    <td>${s.status ?? '-'}</td>
                </tr>`;
        });

        body.innerHTML = `
            <h6>معلومات الدعوى</h6>
            <table class="table table-bordered">
                <tr><th>رقم الدعوى</th><td>${c.number}</td></tr>
                <tr><th>عنوان الدعوى</th><td>${c.title}</td></tr>
                <tr><th>التاريخ الأصلي</th><td>${c.original_date}</td></tr>
            </table>

            <h6 class="mt-4">الجلسات</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>رقم الجلسة</th>
                        <th>وقت الجلسة</th>
                        <th>تاريخ الجلسة</th>
                        <th>سبب الجلسة</th>
                        <th>حالة الجلسة</th>
                    </tr>
                </thead>
                <tbody>${sessionsHTML}</tbody>
            </table>

            <h6 class="mt-4">الأطراف</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>الصفة</th>
                        <th>الاسم</th>
                        <th>التهمة</th>
                    </tr>
                </thead>
                <tbody>${participantsHTML}</tbody>
            </table>
        `;

    } catch (error) {

        console.error(error);

        body.innerHTML = `
            <p class="text-danger text-center">❌ خطأ أثناء تحميل البيانات</p>
        `;
    }
}
</script>




























<script>
document.getElementById('trigger-security').addEventListener('click', function() {
    let menu = document.getElementById('security-menu');
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
    } else {
        menu.style.display = 'none';
    }
});
</script>
@stack('scripts')

</body>
</html>