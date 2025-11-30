<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محضر المحاكمة / ما بعد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        body { padding: 30px; font-family: 'Cairo', sans-serif; }
        .case-number-box { position: absolute; top: 30px; right: 30px; font-weight: bold; }

        .finger-box {
            width: 70px; height: 70px;
            border: 2px dashed #666;
            display: flex;
            justify-content: center; align-items: center;
            margin-top: 10px; border-radius: 8px; opacity: .8;
        }

        .finger-box svg { width: 30px; height: 30px; fill: #666; }
    </style>
</head>
<body>

<!-- رقم الدعوى -->
<div class="case-number-box">
    رقم الدعوى: {{ $case->number }} / {{ $case->year }}
</div>

<!-- هيدر محضر ما بعد -->
<div class="text-center mb-4">
    <h2 class="my-3 fw-bold">محضر المحاكمة / ما بعد</h2>

    <h5 class="mt-3">الهيئة الحاكمة:</h5>
    <p>{{ $judge->full_name }}</p>

    <h5 class="mt-3">الطابعة:</h5>
    <p>{{ $typist->full_name }}</p>
</div>


<form method="POST" action="{{ route('after.trial.report.store', $session->id) }}">
@csrf

<!-- ⭐ نوع المحضر -->
<input type="hidden" name="report_mode" value="after">


<!-- ================================ -->
<!-- 🔷 الأطراف الأساسيين -->
<!-- ================================ -->
@foreach($participants as $part)

@php
    $savedStatement = $reports->firstWhere('participant_id', $part->id);
@endphp

<div class="mb-4">

    <h6>{{ $part->type ?? 'طرف' }}: {{ $part->name }}</h6>

    <label>أقوال الطرف:</label>
    <textarea class="form-control" rows="3" name="participants[{{ $part->id }}][statement]">
{{ $savedStatement->statement_text ?? '' }}
    </textarea>

    <div class="finger-box">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
            <path d="M8 13a.5.5 0 0 1 .5-.5..."></path>
        </svg>
    </div>

</div>
@endforeach


<hr>


<!-- ================================ -->
<!-- 🔷 الأطراف المضافة سابقاً -->
<!-- ================================ -->
<div id="newParties">

@foreach($added_parties as $ap)
<div class="mb-3 border p-3">

    <h6>{{ $ap->role }} : {{ $ap->name }}</h6>

    <label>أقوال الطرف:</label>
    <textarea class="form-control" rows="3" name="new_parties_existing[{{ $ap->id }}][statement]">
{{ $ap->statement_text }}
    </textarea>

    <div class="finger-box mt-2">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
            <path d="M8 13a.5.5 0 0 1..."></path>
        </svg>
    </div>

</div>
@endforeach

</div>


<!-- زر إضافة طرف -->
<button type="button" class="btn btn-secondary mb-3" onclick="addNewParty()">إضافة طرف آخر</button>


<hr>


<!-- ================================ -->
<!-- 🔷 القرار النهائي -->
<!-- ================================ -->
<h5>الــقـــرار</h5>

<label>القرار النهائي:</label>
<textarea class="form-control" rows="3" name="decision_text">
{{ $savedDecision->decision_text ?? '' }}
</textarea>

<div class="finger-box mt-2">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
        <path d="M8 13a.5.5 0 0 1..."></path>
    </svg>
</div>

<hr>

<div class="d-flex gap-3">
    <button type="submit" class="btn btn-primary mt-4">حفظ المحضر</button>
    <a href="{{ route('typist.cases') }}" class="btn btn-danger mt-4">خروج</a>
</div>

</form>


<!-- ================================ -->
<!-- 🔷 JS لإضافة طرف جديد -->
<!-- ================================ -->
<script>
let partyIndex = 0;

function addNewParty() {
    partyIndex++;

    let html = `
    <div class="mb-3 border p-3">

        <h6 id="role_name_${partyIndex}">طرف جديد</h6>

        <label>اسم الطرف:</label>
        <input type="text" class="form-control"
               name="new_parties[${partyIndex}][name]"
               oninput="updateRoleLabel(${partyIndex})">

        <label class="mt-2">نوع الطرف:</label>
        <input type="text" class="form-control"
               name="new_parties[${partyIndex}][role]"
               oninput="updateRoleLabel(${partyIndex})">

        <label class="mt-2">أقوال الطرف:</label>
        <textarea class="form-control" rows="3"
                  name="new_parties[${partyIndex}][statement]"></textarea>

        <div class="finger-box mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                <path d="M8 13a.5.5 0 0 1..."></path>
            </svg>
        </div>

    </div>
    `;

    document.getElementById("newParties").insertAdjacentHTML("beforeend", html);
}

function updateRoleLabel(i) {
    let role = document.querySelector(`input[name='new_parties[${i}][role]']`).value;
    let name = document.querySelector(`input[name='new_parties[${i}][name]']`).value;

    document.getElementById(`role_name_${i}`).textContent =
        (role ? role : "طرف") + " : " + (name ? name : "");
}
</script>

</body>
</html>