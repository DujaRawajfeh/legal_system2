@extends('clerk_dashboard.writer')

@section('title', 'لوحة رئيس القسم')

@section('chief-extra')

<!-- إضافة زر تحويل القضايا داخل قائمة الدعوى/الطلب فقط لرئيس القسم -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const menu = document.getElementById('writer-case-options');

    // نتأكد أنه رئيس القسم (الصفحة هذه أصلا لرئيس القسم)
    if (menu) {

        // إنشاء عنصر جديد للقائمة
        let li = document.createElement("li");
        li.id = "open-transfer-case";
        li.style.padding = "10px";
        li.style.borderBottom = "1px solid #ddd";
        li.style.cursor = "pointer";
        li.textContent = "تحويل الدعاوى من هيئة إلى أخرى";

        // إضافته داخل القائمة
        menu.querySelector("ul").appendChild(li);
    }
});
</script>


<!-- ⭐ نافذة تحويل الدعوى -->
<div class="modal fade" id="transferCaseModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">تحويل الدعوى من هيئة إلى أخرى</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- الهيئة الحالية -->
        <div class="mb-3">
          <label class="form-label fw-bold">الهيئة الحالية</label>
          <select id="current_judge" class="form-select">
            <option value="">اختر القاضي الحالي...</option>
          </select>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label>رقم الدعوى</label>
            <input type="text" id="transfer_case_number" class="form-control" placeholder="أدخل رقم الدعوى">
          </div>

          <div class="col-md-6">
            <label>سنة الدعوى</label>
            <input type="text" id="transfer_case_year" class="form-control" placeholder="أدخل السنة">
          </div>
        </div>

        <hr>

        <!-- الهيئة الجديدة -->
        <div class="mb-3">
          <label class="form-label fw-bold">الهيئة الجديدة</label>
          <select id="new_judge" class="form-select">
            <option value="">اختر القاضي الجديد...</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button id="save_transfer" class="btn btn-success">💾 حفظ التحويل</button>
        <button class="btn btn-danger" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

<script>
    //نافذه تحويل دعوى
document.addEventListener("DOMContentLoaded", function () {

    // فتح نافذة التحويل من القائمة
    document.addEventListener("click", function(e){
        if (e.target && e.target.id === "open-transfer-case") {

            let modal = new bootstrap.Modal(document.getElementById("transferCaseModal"));
            modal.show();

            // تحميل القضاة
            axios.get("/chief/judges")
            .then(res => {
                let judges = res.data.judges;

                let currentSelect = document.getElementById("current_judge");
                let newSelect     = document.getElementById("new_judge");

                currentSelect.innerHTML = `<option value="">اختر القاضي الحالي...</option>`;
                newSelect.innerHTML     = `<option value="">اختر القاضي الجديد...</option>`;

                judges.forEach(j => {
                    currentSelect.innerHTML += `<option value="${j.id}">${j.full_name}</option>`;
                    newSelect.innerHTML     += `<option value="${j.id}">${j.full_name}</option>`;
                });
            })
            .catch(() => {
                alert("❌ خطأ أثناء جلب القضاة");
            });
        }
    });

    // حفظ التحويل
    document.getElementById("save_transfer").addEventListener("click", () => {

        let currentJudge = document.getElementById("current_judge").value;
        let newJudge     = document.getElementById("new_judge").value;
        let number       = document.getElementById("transfer_case_number").value;
        let year         = document.getElementById("transfer_case_year").value;

        if (!currentJudge || !newJudge || !number || !year) {
            alert("⚠️ يرجى تعبئة جميع الحقول");
            return;
        }

        axios.post("/chief/transfer-case", {
            case_number: number,
            case_year: year,
            old_judge_id: currentJudge,
            new_judge_id: newJudge
        })
        .then(() => {
            alert("✔ تم تحويل الدعوى بنجاح");
        })
        .catch(() => {
            alert("❌ حدث خطأ أثناء التحويل");
        });

    });

});
</script>







<!--  زر تعيين قاضي داخل قائمة الدعوى/الطلب -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const menu = document.getElementById('writer-case-options');

    if (menu) {

        let li = document.createElement("li");
        li.id = "open-assign-judge";
        li.style.padding = "10px";
        li.style.borderBottom = "1px solid #ddd";
        li.style.cursor = "pointer";
        li.textContent = "تعيين قاضي للكاتب / الطابعة";

        menu.querySelector("ul").appendChild(li);
    }

});
</script>
<!--  نافذة تحديد القضاة للكاتب / الطابعة -->
<div class="modal fade" id="assignJudgeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">تحديد القضاة للكاتب / الطابعة</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!--  اختيار الكاتب أو الطابعة -->
        <div class="d-flex gap-3 mb-3">
            <button class="btn btn-outline-primary" id="chooseWriterBtn">الكاتب</button>
            <button class="btn btn-outline-secondary" id="chooseTypistBtn">الطابعة</button>
        </div>

        <!--  اختيار الكاتب -->
        <div id="writerSection" class="d-none">
            <h6 class="fw-bold mb-2">اختر الكاتب</h6>
            <select id="writerSelect" class="form-select mb-3"></select>

            <h6 class="fw-bold mb-2">اختر القاضي</h6>
            <select id="writerJudgeSelect" class="form-select mb-3"></select>

            <button class="btn btn-success" id="saveWriterJudge">💾 حفظ</button>
        </div>

        <!-- اختيار الطابعة -->
        <div id="typistSection" class="d-none">
            <h6 class="fw-bold mb-2">اختر الطابعة</h6>
            <select id="typistSelect" class="form-select mb-3"></select>

            <h6 class="fw-bold mb-2">اختر القاضي</h6>
            <select id="typistJudgeSelect" class="form-select mb-3"></select>

            <button class="btn btn-success" id="saveTypistJudge">💾 حفظ</button>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-danger" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>
<script>  
document.addEventListener("DOMContentLoaded", () => {  

    // ⭐ فتح نافذة تعيين القاضي من القائمة  
    document.addEventListener("click", function(e){  
        if (e.target && e.target.id === "open-assign-judge") {  

            let modal = new bootstrap.Modal(document.getElementById("assignJudgeModal"));  
            modal.show();  

            // تحميل الموظفين والقضاة  
            loadWriters();  
            loadTypists();  
            loadJudges();  
        }  
    });  

    // ⭐ زر اختيار الكاتب  
    document.getElementById("chooseWriterBtn").addEventListener("click", () => {  
        document.getElementById("writerSection").classList.remove("d-none");  
        document.getElementById("typistSection").classList.add("d-none");  
    });  

    // ⭐ زر اختيار الطابعة  
    document.getElementById("chooseTypistBtn").addEventListener("click", () => {  
        document.getElementById("typistSection").classList.remove("d-none");  
        document.getElementById("writerSection").classList.add("d-none");  
    });  

    // ⭐ تحميل الكتاب  
    function loadWriters() {  
        axios.get("/chief/employees?role=writer")  
        .then(res => {  
            let users = res.data.users;  
            let select = document.getElementById("writerSelect");  

            select.innerHTML = "";  
            users.forEach(u => {  
                select.innerHTML += `<option value="${u.id}">${u.full_name}</option>`;  
            });  
        })  
        .catch(err => { 
            console.error("❌ ERROR loadWriters:", err);
            alert("❌ خطأ أثناء تحميل الكتاب"); 
        });
    }  

    // ⭐ تحميل الطابعات  
    function loadTypists() {  
        axios.get("/chief/employees?role=typist")  
        .then(res => {  
            let users = res.data.users;  
            let select = document.getElementById("typistSelect");  

            select.innerHTML = "";  
            users.forEach(u => {  
                select.innerHTML += `<option value="${u.id}">${u.full_name}</option>`;  
            });  
        })  
        .catch(err => { 
            console.error("❌ ERROR loadTypists:", err);
            alert("❌ خطأ أثناء تحميل الطابعات");
        });
    }  

    // ⭐ تحميل القضاة  
    function loadJudges() {  
        axios.get("/chief/judges")  
        .then(res => {  
            let judges = res.data.judges;  

            let wS = document.getElementById("writerJudgeSelect");  
            let tS = document.getElementById("typistJudgeSelect");  

            wS.innerHTML = "";  
            tS.innerHTML = "";  

            judges.forEach(j => {  
                wS.innerHTML += `<option value="${j.id}">${j.full_name}</option>`;  
                tS.innerHTML += `<option value="${j.id}">${j.full_name}</option>`;  
            });  
        })  
        .catch(err => { 
            console.error("❌ ERROR loadJudges:", err);
            alert("❌ خطأ أثناء تحميل القضاة"); 
        });
    }  

    // ⭐ حفظ القاضي للكاتب  
    document.getElementById("saveWriterJudge").addEventListener("click", () => {  

        axios.post("/chief/assign-judge", {  
            employee_id: document.getElementById("writerSelect").value,  
            judge_id: document.getElementById("writerJudgeSelect").value  
        })  
        .then(() => alert("✔ تم حفظ القاضي للكاتب"))  
        .catch(err => {
            console.error("❌ ERROR saveWriterJudge RESPONSE:", err.response);
            console.error("❌ ERROR saveWriterJudge DATA:", err.response?.data);
            alert("❌ خطأ أثناء الحفظ");
        });
    });  

    // ⭐ حفظ القاضي للطابعة  
    document.getElementById("saveTypistJudge").addEventListener("click", () => {  

        axios.post("/chief/assign-judge", {  
            employee_id: document.getElementById("typistSelect").value,  
            judge_id: document.getElementById("typistJudgeSelect").value  
        })  
        .then(() => alert("✔ تم حفظ القاضي للطابعة"))  
        .catch(err => {
            console.error("❌ ERROR saveTypistJudge RESPONSE:", err.response);
            console.error("❌ ERROR saveTypistJudge DATA:", err.response?.data);
            alert("❌ خطأ أثناء الحفظ");
        });
    });  

});  
</script>








<!-- جدول الموقوفين -->
<div class="card mt-4">
    <div class="card-header bg-danger text-white">
        <h4 class="mb-0">الموقوفون المنتهية فترة توقيفهم أو قاربت على الانتهاء</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" id="detainedTable">
                <thead class="table-dark">
                    <tr>
                        <th>اسم الموقوف</th>
                        <th>تاريخ التوقيف</th>
                        <th>مدة التوقيف (يوم)</th>
                        <th>تاريخ انتهاء التوقيف</th>
                        <th>المدة المتبقية</th>
                        <th>رقم الدعوى</th>
                        <th>الدعوى التي سببت التوقيف</th>
                    </tr>
                </thead>

                <tbody id="detainedBody">
                    <tr>
                        <td colspan="7">جاري التحميل...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    function loadDetainedTable() {
        axios.get("/chief/detained-list")
            .then(res => {
                let data = res.data.data;
                let tbody = document.querySelector("#detainedBody");
                tbody.innerHTML = "";

                data.forEach(row => {

                    // 🔵 تحديد اللون حسب حالة المدة
                    let color = "black";
                    if (row.remaining_days < 0) color = "red";
                    else if (row.remaining_days <= 3) color = "orange";

                    // 🔵 تعبئة الصفوف
                    tbody.innerHTML += `
                        <tr>
                            <td>${row.participant_name}</td>
                            <td>${row.start_date}</td>
                            <td>${row.duration}</td>
                            <td>${row.end_date}</td>

                            <td style="color:${color}; font-weight:bold;">
                                ${
                                    row.remaining_days < 0
                                    ? "منتهٍ"
                                    : Math.floor(row.remaining_days) + " يوم"
                                }
                            </td>

                            <td>${row.case_number}</td>
                            <td>${row.case_type}</td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                console.error(err);
                alert("❌ خطأ أثناء تحميل بيانات الموقوفين");
            });
    }

    // 🔵 تحميل الجدول عند فتح الصفحة
    loadDetainedTable();

});
</script>
@endsection