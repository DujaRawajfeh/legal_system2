<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>صفحة المؤرشف</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">

    {{-- 🔷 الشريط العلوي لعرض اسم المؤرشف والمحكمة والقلم --}}
    @php
        $firstCase = $cases->first();
    @endphp
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5>المؤرشف: {{ auth()->user()->full_name }}</h5>
            <h6>المحكمة: {{ $firstCase->tribunal->name ?? '-' }}</h6>
            <h6>القلم: {{ $firstCase->department->name ?? '-' }}</h6>
        </div>
    </div>

    {{-- ✅ عرض الأخطاء --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 📋 نموذج إدخال وثيقة مؤرشفة --}}
    <div class="card">
        <div class="card-header">إدخال وثيقة جديدة</div>
        <div class="card-body">
            <form method="POST" action="{{ route('archived-documents.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- 🔢 اختيار رقم القضية --}}
                <div class="mb-3">
                    <label for="court_case_id" class="form-label">رقم القضية</label>
                    <select name="court_case_id" id="court_case_id" class="form-select" required>
                        <option value="">-- اختر رقم القضية --</option>
                        @foreach ($cases as $case)
                            <option value="{{ $case->id }}">{{ $case->number }} - {{ $case->type }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 📄 نوع الوثيقة --}}
                <div class="mb-3">
                    <label for="document_type" class="form-label">نوع الوثيقة</label>
                    <select name="document_type" id="document_type" class="form-select" required>
                        <option value="">-- اختر نوع الوثيقة --</option>
                        <option>مسودة قرار</option>
                        <option>قرارات و أحكام</option>
                        <option>قرار تصحيح خطأ مادي</option>
                        <option>وصولات مالية</option>
                        <option>مستندات الصرف</option>
                        <option>ملف محال في محكمة اخرى</option>
                        <option>كتب رسمية</option>
                        <option>إستدعاءات</option>
                        <option>لائحه جوابية</option>
                        <option>لائحه الدعوى</option>
                        <option>تعهد صحة بيانات و أوراق شخصية</option>
                        <option>بيانات المشتكى</option>
                        <option>بيانات المشتكى عليه</option>
                        <option>تباليغ و مذكرات</option>
                        <option>محاضر و جلسات</option>
                        <option>تقارير خبرة</option>
                        <option>أدلة جنائية</option>
                        <option>بيانات إضافية</option>
                    </select>
                </div>

                {{-- 📎 رفع ملف PDF --}}
                <div class="mb-3">
                    <label for="document_file" class="form-label">رفع ملف PDF</label>
                    <input type="file" name="document_file" id="document_file" class="form-control" accept="application/pdf" required>
                </div>

                <button type="submit" class="btn btn-success">📤 أرشفة الوثيقة</button>
            </form>
        </div>
    </div>

    {{-- 📂 عرض الوثائق المؤرشفة --}}
    <div class="card mt-4">
        <div class="card-header">الوثائق المؤرشفة</div>
        <div class="card-body">
            @if ($documents->count())
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>رقم الدعوى</th>
                            <th>رقم الوثيقة</th>
                            <th>نوع الوثيقة</th>
                            <th>تاريخ/وقت الأرشفة</th>
                            <th>عرض</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $doc)
                            <tr>
                                <td>{{ $doc->courtCase->number ?? $doc->court_case_id }}</td>
                                <td>{{ $doc->document_number }}</td>
                                <td>{{ $doc->document_type }}</td>
                                <td>{{ $doc->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ asset('uploads/archived_documents/' . $doc->file_name) }}" target="_blank" class="btn btn-sm btn-primary"> عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>لا توجد وثائق مؤرشفة بعد.</p>
            @endif
        </div>
    </div>

</div>
</body>
</html>