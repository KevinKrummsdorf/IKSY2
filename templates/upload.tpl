{extends file="./layouts/layout.tpl"}
{block name="title"}Material hochladen{/block}

{block name="content"}
<div class="container">
    <h1 class="mb-4 text-center">Material hochladen</h1>

    {if isset($error)}
    <div class="alert alert-danger">{$error}</div>
    {/if}
    {if isset($success)}
    <div class="alert alert-success">{$success}</div>
    {/if}

    <form action="{$base_url}/upload.php" method="post" enctype="multipart/form-data" onsubmit="return validateUploadForm(event)">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">

        <div class="mb-3">
            <label for="action" class="form-label">Aktion wählen</label>
            <select id="action" name="action" class="form-select" required onchange="toggleAction(this.value)">
                <option value="" disabled selected>Bitte wählen...</option>
                <option value="document">Dokument hochladen</option>
                <option value="group">Dokument für Lerngruppe hochladen</option>
                <option value="course">Kursvorschlag einreichen</option>
            </select>
        </div>

        <div id="document-fields" style="display:none;">
            <div class="mb-3">
                <label for="title" class="form-label">Titel</label>
                <input type="text" id="title" name="title" class="form-control" value="{$title|escape}">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Beschreibung</label>
                <textarea id="description" name="description" class="form-control" rows="3">{$description|escape}</textarea>
            </div>

            <div class="mb-3">
                <label for="course" class="form-label">Kurs</label>
                <select id="course" name="course" class="form-select" onchange="toggleCustomCourse(this.value)">
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
                <input type="file" id="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt">
                <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT Max. 10 MB.</div>
            </div>
        </div>

        <div id="group-fields" style="display:none;">
            <div class="mb-3">
                <label for="group_title" class="form-label">Titel</label>
                <input type="text" id="group_title" name="group_title" class="form-control">
            </div>
            <div class="mb-3">
                <label for="group_description" class="form-label">Beschreibung</label>
                <textarea id="group_description" name="group_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="group_name" class="form-label">Lerngruppe</label>
                <input type="text" id="group_name" name="group_name" class="form-control">
            </div>
            <div class="mb-3">
                <label for="group_file" class="form-label">Datei auswählen</label>
                <input type="file" id="group_file" name="group_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt">
            </div>
        </div>

        <div id="suggest-fields" style="display:none;">
            <div class="mb-3">
                <label for="course_suggestion" class="form-label">Kursvorschlag</label>
                <input type="text" id="course_suggestion" name="custom_course" class="form-control" value="{$customCourse|escape}" placeholder="z. B. Informatik 1">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Absenden</button>
    </form>
</div>

{literal}
<script>
function toggleCustomCourse(value) {
    const wrapper = document.getElementById('custom-course-wrapper');
    if (wrapper) {
        wrapper.style.display = (value === '__custom__') ? 'block' : 'none';
    }
}

function toggleAction(action) {
    document.getElementById('document-fields').style.display = (action === 'document') ? 'block' : 'none';
    document.getElementById('group-fields').style.display    = (action === 'group') ? 'block' : 'none';
    document.getElementById('suggest-fields').style.display  = (action === 'course') ? 'block' : 'none';
}

function validateUploadForm(event) {
    const action = document.getElementById('action').value;
    let messages = [];

    if (action === 'document') {
        if (!document.getElementById('title').value.trim()) messages.push('Titel fehlt.');
        if (!document.getElementById('course').value) messages.push('Kurs fehlt.');
        if (document.getElementById('course').value === '__custom__' && !document.getElementById('custom_course').value.trim()) {
            messages.push('Kursvorschlag fehlt.');
        }
        if (!document.getElementById('file').value) messages.push('Datei fehlt.');
    } else if (action === 'group') {
        if (!document.getElementById('group_title').value.trim()) messages.push('Titel fehlt.');
        if (!document.getElementById('group_name').value.trim()) messages.push('Lerngruppe fehlt.');
        if (!document.getElementById('group_file').value) messages.push('Datei fehlt.');
    } else if (action === 'course') {
        if (!document.getElementById('course_suggestion').value.trim()) messages.push('Kursvorschlag fehlt.');
    } else {
        messages.push('Bitte Aktion wählen.');
    }

    if (messages.length > 0) {
        event.preventDefault();
        alert(messages.join('\n'));
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    toggleAction(document.getElementById('action').value);
    const courseSelect = document.getElementById('course');
    if (courseSelect) {
        toggleCustomCourse(courseSelect.value);
    }
});
</script>
{/literal}
{/block}
