{extends file="./layouts/layout.tpl"}
{block name="title"}Upload & Kursvorschlag{/block}

{block name="content"}
<div class="container">
    <h1 class="mb-4 text-center">Materialien hochladen</h1>

    {if isset($error)}
    <div class="alert alert-danger">{$error}</div>
    {/if}
    {if isset($success)}
    <div class="alert alert-success">{$success}</div>
    {/if}

    <div class="mb-3">
        <label for="action" class="form-label">Aktion wählen</label>
        <select id="action" name="action" class="form-select" onchange="toggleAction(this.value)">
            <option value="upload" {if $action == 'upload'}selected{/if}>Material hochladen</option>
            <option value="suggest" {if $action == 'suggest'}selected{/if}>Kurs vorschlagen</option>
        </select>
    </div>

    <form id="upload-form" action="{$base_url}/upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="action" value="upload">

        <div class="mb-3">
            <label for="title" class="form-label">Titel</label>
            <input type="text" id="title" name="title" class="form-control" value="{$title|escape}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Beschreibung</label>
            <textarea id="description" name="description" class="form-control" rows="3">{$description|escape}</textarea>
        </div>

        <div class="mb-3">
            <label for="course" class="form-label">Kurs</label>
            <select id="course" name="course" class="form-select" required onchange="toggleCustomCourse(this.value)">
                <option value="" disabled {if !$selectedCourse}selected{/if}>Bitte wählen...</option>
                {foreach from=$courses item=course}
                    <option value="{$course.value|escape}" {if $course.value == $selectedCourse}selected{/if}>{$course.name|escape}</option>
                {/foreach}
                <option value="__custom__" {if $selectedCourse == '__custom__'}selected{/if}>Anderer (bitte angeben)</option>
            </select>
        </div>

        <div class="mb-3" id="custom-course-wrapper" style="display: none;">
            <label for="custom_course" class="form-label">Kursvorschlag</label>
            <input type="text" id="custom_course" name="custom_course" class="form-control" value="{$customCourse|escape}" placeholder="z. B. Informatik 1">
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">Datei auswählen</label>
            <input type="file" id="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT Max. 10 MB.</div>
        </div>

        <div class="mb-3" id="ppt-convert-wrapper" style="display:none;">
            <label for="convert_ppt" class="form-label">PowerPoint in PDF umwandeln?</label>
            <select id="convert_ppt" name="convert_ppt" class="form-select">
                <option value="1" selected>Ja</option>
                <option value="0">Nein</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>

    <form id="suggest-form" action="{$base_url}/upload.php" method="post" style="display:none;">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="action" value="suggest">

        <div class="mb-3">
            <label for="course_suggestion" class="form-label">Kursname</label>
            <input type="text" id="course_suggestion" name="course_suggestion" class="form-control" value="{$courseSuggestion|escape}">
        </div>

        <button type="submit" class="btn btn-primary">Vorschlagen</button>
    </form>
</div>

{literal}
<script>
function toggleCustomCourse(value) {
    const wrapper = document.getElementById('custom-course-wrapper');
    wrapper.style.display = (value === '__custom__') ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    toggleCustomCourse(document.getElementById('course').value);
    toggleAction(document.getElementById('action').value);
    checkPpt(document.getElementById('file').value);
    document.getElementById('file').addEventListener('change', function(){
        checkPpt(this.value);
    });
});

function toggleAction(val) {
    const uploadForm = document.getElementById('upload-form');
    const suggestForm = document.getElementById('suggest-form');
    if (val === 'suggest') {
        uploadForm.style.display = 'none';
        suggestForm.style.display = 'block';
    } else {
        uploadForm.style.display = 'block';
        suggestForm.style.display = 'none';
    }
}

function checkPpt(filename) {
    const wrapper = document.getElementById('ppt-convert-wrapper');
    const lower = filename.toLowerCase();
    const show = lower.endsWith('.ppt') || lower.endsWith('.pptx');
    wrapper.style.display = show ? 'block' : 'none';
}
</script>
{/literal}
{/block}
