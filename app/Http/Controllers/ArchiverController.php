<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArchivedDocument;

class ArchiverController extends Controller
{
   public function index()
{
    $archiver = auth()->user();

    // جلب القضايا مع المحكمة والقلم المرتبطين بها
    $cases = \App\Models\CourtCase::with(['tribunal', 'department'])->get();

    // جلب الوثائق المؤرشفة
    $documents = ArchivedDocument::latest()->get();

    // إرسال البيانات للواجهة
    return view('clerk_dashboard.archiver', compact('cases', 'archiver', 'documents'));
}

public function store(Request $request)
{
    $request->validate([
        'court_case_id' => 'required|integer',
        'document_type' => 'required|string|max:255',
        'document_file' => 'required|mimes:pdf|max:10240',
    ]);

    $case = \App\Models\CourtCase::find($request->court_case_id);

    if (!$case) {
        return back()->withErrors(['court_case_id' => 'رقم القضية غير موجود في قاعدة البيانات'])->withInput();
    }

    // توليد رقم الوثيقة حسب عدد الوثائق داخل نفس القضية
    $existingCount = ArchivedDocument::where('court_case_id', $case->id)->count();
    $documentNumber = $case->number . '/' . ($existingCount + 1);

    // ✅ توليد اسم فريد للملف
    $file = $request->file('document_file');
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = $file->getClientOriginalExtension();
    $uniqueName = $originalName . '_' . time() . '.' . $extension;

    // تخزين الملف فعليًا في مجلد الأرشفة
    $file->move(public_path('uploads/archived_documents'), $uniqueName);

    // تخزين الاسم فقط في قاعدة البيانات
    ArchivedDocument::create([
        'court_case_id'   => $request->court_case_id,
        'document_type'   => $request->document_type,
        'file_name'       => $uniqueName, // ✅ بدل file_path
        'document_number' => $documentNumber,
    ]);

    return redirect()->route('archiver.page')->with('success', 'تمت أرشفة الوثيقة بنجاح');
}
}