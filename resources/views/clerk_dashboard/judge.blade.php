<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>صفحة القاضي</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ⭐ تصغير الجداول */
        table.table {
            font-size: 13px;
        }
        table.table td, table.table th {
            padding: 6px 8px !important;
        }
        table.table thead th {
            font-size: 13px;
        }
        .card-header {
            font-size: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-4">

    {{-- 🔷 معلومات القاضي --}}
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5>القاضي: {{ $judge->full_name }}</h5>
            <h6>المحكمة: {{ $judge->tribunal->name ?? '-' }}</h6>
            <h6>القلم: {{ $judge->department->name ?? '-' }}</h6>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 🔵 جدول طلبات اليوم -->
    <!-- ========================================================= -->
    <div class="card mb-4">
        <div class="card-header">طلبات اليوم</div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="todayRequestsTable">
                <thead class="table-dark">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>عنوان الطلب</th>
                        <th>التاريخ الأصلي</th>
                        <th>وقت الجلسة</th>
                        <th>نوع الجلسة</th>
                        <th>حالة الجلسة</th>
                        <th>سبب التأجيل</th>
                    </tr>
                </thead>
                <tbody id="todayRequestsBody">
                    <tr><td colspan="7" class="text-center">جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 🟣 جدول الطلبات الكاملة + الأطراف + الأحكام -->
    <!-- ========================================================= -->
    <div class="card mb-5">
        <div class="card-header">تفاصيل الطلبات</div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="allRequestsTable">
               <thead class="table-dark">
    <tr>
        <th>رقم الطلب</th>
        <th>عنوان الطلب</th>
        <th>نوع الطرف</th>
        <th>اسم الطرف</th>
        <th>تاريخ/وقت الجلسة</th>   <!--  تمت الإضافة -->
        <th>تاريخ الحكم</th>
        <th>تاريخ الإغلاق</th>
        <th>الحكم ضد الأطراف</th>
        <th>الحكم الفاصل</th>
        <th>إسقاط الحق الشخصي</th>
    </tr>
</thead>
                <tbody id="allRequestsBody">
                    <tr><td colspan="9" class="text-center">جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 🔍 بحث الجلسات -->
    <!-- ========================================================= -->
    <div class="mb-3">
        <input type="text" class="form-control" id="searchSessions" placeholder="🔍 ابحث برقم الدعوى في جدول الجلسات">
    </div>

    <!-- ========================================================= -->
    <!-- 📋 جدول الجلسات -->
    <!-- ========================================================= -->
    <div class="card">
        <div class="card-header">جلسات اليوم</div>
        <div class="card-body">
            <table id="sessionsTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>رقم الدعوى</th>
                        <th>عنوان الدعوى</th>
                        <th>التاريخ الأصلي</th>
                        <th>وقت الجلسة</th>
                        <th>نوع الجلسة</th>
                        <th>الحالة</th>
                        <th>سبب التأجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->courtCase->number ?? '-' }}</td>
                            <td>{{ $session->courtCase->type ?? '-' }}</td>
                            <td>{{ $session->courtCase->created_at->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->session_date)->format('H:i') }}</td>
                            <td>{{ $session->session_type }}</td>
                            <td>{{ $session->status }}</td>
                            <td>
                                @if($session->status === 'مؤجلة')
                                    {{ $session->postponed_reason }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">لا توجد جلسات اليوم</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 🔍 بحث القضايا -->
    <!-- ========================================================= -->
    <div class="mb-3 mt-5">
        <input type="text" class="form-control" id="searchCases" placeholder="🔍 ابحث برقم الدعوى في جدول القضايا">
    </div>

    <!-- ========================================================= -->
    <!-- 📋 جدول القضايا -->
    <!-- ========================================================= -->
    <div class="card mt-2">
        <div class="card-header">تفاصيل القضايا المرتبطة بالقاضي</div>
        <div class="card-body">
            <table id="casesTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>رقم الدعوى</th>
                        <th>عنوان الدعوى</th>
                        <th>نوع الطرف</th>
                        <th>اسم الطرف</th>
                        <th>التهمة</th>
                        <th>مدة التوقيف</th>
                        <th>سبب التوقيف</th>
                        <th>تم الإفراج عنه</th>
                        <th>مركز الإصلاح</th>
                        <th>طريقة التبليغ</th>
                        <th>تاريخ التبليغ</th>
                        <th>محضر المحاكمة</th>
                        <th>التاريخ الأصلي</th>
                        <th>تاريخ / وقت الجلسة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $case)
                        @foreach ($case->participants as $index => $participant)
                            @php
                                $memo = $case->arrestMemos->firstWhere('participant_name', $participant->name);
                                $notification = $case->notifications->firstWhere('participant_name', $participant->name);
                                $firstSession = $case->sessions->first();
                            @endphp
                            <tr>
                                <td>{{ $case->number }}</td>
                                <td>{{ $case->type }}</td>
                                <td>طرف {{ $index + 1 }} - {{ $participant->type }}</td>
                                <td>{{ $participant->name }}</td>
                                <td>{{ $participant->charge }}</td>
                                <td>{{ $memo->detention_duration ?? '-' }}</td>
                                <td>{{ $memo->detention_reason ?? '-' }}</td>
                                <td>{{ $memo->released ?? '-' }}</td>
                                <td>{{ $memo->detention_center ?? '-' }}</td>
                                <td>{{ $notification->method ?? '-' }}</td>
                                <td>{{ $notification && $notification->notified_at ? \Carbon\Carbon::parse($notification->notified_at)->format('Y-m-d') : '-' }}</td>

                                <td>
                                    @if($firstSession)

                                        @if(\App\Models\CourtSessionReport::where('case_session_id', $firstSession->id)->where('report_mode','trial')->exists())
                                            <a href="{{ route('judge.trial.report', $firstSession->id) }}"
                                               class="btn btn-sm btn-outline-primary mb-1">
                                                محضر المحاكمة
                                            </a>
                                        @endif

                                        @if(\App\Models\CourtSessionReport::where('case_session_id', $firstSession->id)->where('report_mode','after')->exists())
                                            <a href="{{ route('judge.after.report', $firstSession->id) }}"
                                               class="btn btn-sm btn-outline-secondary mb-1">
                                                محضر ما بعد
                                            </a>
                                        @endif

                                        @if(!\App\Models\CourtSessionReport::where('case_session_id',$firstSession->id)->exists())
                                            <span class="text-muted">لا يوجد محضر</span>
                                        @endif

                                    @else
                                        <span class="text-muted">لا يوجد جلسة</span>
                                    @endif
                                </td>

                                <td>{{ $case->created_at ? $case->created_at->format('Y-m-d') : '-' }}</td>
                                <td>{{ $firstSession ? \Carbon\Carbon::parse($firstSession->session_date)->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="14" class="text-center">لا توجد قضايا مرتبطة بهذا القاضي</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- ⭐ JavaScript لجلب الطلبات -->
<!-- ========================================================= -->
<!-- تحميل axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log("📌 Judge page JS loaded");
    loadTodayRequests();
    loadAllRequests();
});

// -------- جدول طلبات اليوم --------
async function loadTodayRequests() {
    const body = document.getElementById("todayRequestsBody");

    try {
        console.log("🔹 Calling: {{ route('judge.requests.today') }}");

        const response = await axios.get("{{ route('judge.requests.today') }}");
        console.log("✅ Today Requests Response:", response);

        // لو الـ JSON ما فيه requests نعاملها كـ مصفوفة فاضية
        const data = response.data.requests || [];

        if (!Array.isArray(data)) {
            body.innerHTML = `
                <tr>
                    <td colspan="7" class="text-danger text-center">
                        تنسيق البيانات غير متوقع من السيرفر
                    </td>
                </tr>`;
            return;
        }

        let html = "";
        data.forEach(r => {
            html += `
                <tr>
                    <td>${r.request_number ?? '-'}</td>
                    <td>${r.title ?? '-'}</td>
                    <td>${(r.created_at || '').toString().substring(0,10) || '-'}</td>
                    <td>${r.session_time ?? '-'}</td>
                    <td>${r.session_type ?? '-'}</td>
                    <td>${r.session_status ?? '-'}</td>
                    <td>${r.session_reason ?? '-'}</td>
                </tr>
            `;
        });

        body.innerHTML = html || `
            <tr>
                <td colspan="7" class="text-center">لا يوجد طلبات اليوم</td>
            </tr>
        `;

    } catch (err) {
        console.error("❌ ERROR in loadTodayRequests:", err);

        const status  = err.response ? err.response.status : '؟';
        const message = err.message || 'خطأ غير معروف';

        body.innerHTML = `
            <tr>
                <td colspan="7" class="text-danger text-center">
                    خطأ أثناء تحميل البيانات (status: ${status}) - ${message}
                </td>
            </tr>
        `;
    }
}

// -------- جدول كل الطلبات + الأطراف + الأحكام --------
async function loadAllRequests() {
    const body = document.getElementById("allRequestsBody");

    try {
        console.log("🔹 Calling: {{ route('judge.requests.all') }}");

        const response = await axios.get("{{ route('judge.requests.all') }}");
        console.log("✅ All Requests Response:", response);

        const data = response.data.requests || [];

        if (!Array.isArray(data)) {
            body.innerHTML = `
                <tr>
                    <td colspan="10" class="text-danger text-center">
                        تنسيق البيانات غير متوقع من السيرفر
                    </td>
                </tr>`;
            return;
        }

        let html = "";

        data.forEach(r => {
            const parties = [
                {label: 'مشتكي',       name: r.plaintiff_name,   text: r.judgment_text_plaintiff},
                {label: 'مشتكى عليه',  name: r.defendant_name,   text: r.judgment_text_defendant},
                {label: 'طرف ثالث',    name: r.third_party_name, text: r.judgment_text_third_party},
                {label: 'محامي',        name: r.lawyer_name,      text: r.judgment_text_lawyer},
            ];

            parties.forEach(p => {

                html += `
                    <tr>
                        <td>${r.request_number ?? '-'}</td>
                        <td>${r.title ?? '-'}</td>

                        <td>${p.label}</td>
                        <td>${p.name ?? '-'}</td>

                       <td>${r.session_date && r.session_time ? r.session_date + ' / ' + r.session_time : '-'}</td>

                        <td>${r.judgment_date ?? '-'}</td>
                        <td>${r.closure_date ?? '-'}</td>

                        <td>${p.text ?? '-'}</td>
                        <td>${r.judgment_text_final ?? '-'}</td>
                        <td>${r.judgment_text_waiver ?? '-'}</td>
                    </tr>
                `;
            });
        });

        body.innerHTML = html || `
            <tr>
                <td colspan="10" class="text-center">لا توجد طلبات</td>
            </tr>
        `;

    } catch (err) {
        console.error("❌ ERROR in loadAllRequests:", err);

        const status  = err.response ? err.response.status : '؟';
        const message = err.message || 'خطأ غير معروف';

        body.innerHTML = `
            <tr>
                <td colspan="10" class="text-danger text-center">
                    خطأ أثناء تحميل البيانات (status: ${status}) - ${message}
                </td>
            </tr>`;
    }
}
</script>

<!-- فلترة الجداول الأصلية -->
<script>
document.getElementById('searchSessions').addEventListener('input', function () {
    const value = this.value.trim();
    const rows = document.querySelectorAll('#sessionsTable tbody tr');
    rows.forEach(row => {
        const cell = row.querySelector('td');
        row.style.display = cell && cell.textContent.includes(value) ? '' : 'none';
    });
});

document.getElementById('searchCases').addEventListener('input', function () {
    const value = this.value.trim();
    const rows = document.querySelectorAll('#casesTable tbody tr');
    rows.forEach(row => {
        const cell = row.querySelector('td');
        row.style.display = cell && cell.textContent.includes(value) ? '' : 'none';
    });
});
</script>

</body>
</html>