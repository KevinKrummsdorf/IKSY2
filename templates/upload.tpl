{extends file="./layouts/layout.tpl"}
{block name="title"}Upload & Kursvorschlag{/block}

{block name="content"}
<div class="container">
    <h1 class="mb-4 text-center">Materialien hochladen</h1>

    {if isset($error)}
    <div class="alert alert-danger">{$error}</div>
    {/if}
    {if isset($warning)}
    <div class="alert alert-warning">{$warning}</div>
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

    <form id="upload-form" action="{url path='upload'}" method="post" enctype="multipart/form-data">
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
            </select>
        </div>

        <div class="mb-3" id="custom-course-wrapper" style="display: none;">
            <label for="custom_course" class="form-label">Kursvorschlag</label>
            <input type="text" id="custom_course" name="custom_course" class="form-control" value="{$customCourse|escape}" placeholder="z. B. Informatik 1">
        </div>

        {if $userGroups|@count > 0}
        <div class="mb-3">
            <label for="upload_target" class="form-label">Upload-Ziel</label>
            <select id="upload_target" name="upload_target" class="form-select" onchange="toggleGroupSelect(this.value)">
                <option value="public" {if $uploadTarget != 'group'}selected{/if}>Normal hochladen</option>
                <option value="group" {if $uploadTarget == 'group'}selected{/if}>Eine Lerngruppe hochladen</option>
            </select>
        </div>
        <div class="mb-3" id="group-select" style="display:none;">
            <label for="group_id" class="form-label">Lerngruppe</label>
            <select id="group_id" name="group_id" class="form-select">
                {foreach from=$userGroups item=g}
                    <option value="{$g.id}" {if $selectedGroupId == $g.id}selected{/if}>{$g.name|escape}</option>
                {/foreach}
            </select>
        </div>
        {/if}

        <div class="mb-3">
        <input type="file" id="file" name="file" class="form-control"
            accept=".pdf,.jpg,.jpeg,.png,.txt,.doc,.docx,.odt,.ppt,.pptx" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT, PPTX Max. 10 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>

    <form id="suggest-form" action="{url path='upload'}" method="post" style="display:none;">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="action" value="suggest">
        {if isset($warning)}
        <input type="hidden" name="confirm_similar" value="1">
        {/if}

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
    const tgt = document.getElementById('upload_target');
    if (tgt) {
        toggleGroupSelect(tgt.value);
    }
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

function toggleGroupSelect(val) {
    const wrapper = document.getElementById('group-select');
    if (wrapper) {
        wrapper.style.display = (val === 'group') ? 'block' : 'none';
    }
}
</script>
{/literal}
{/block}
