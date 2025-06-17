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
            <option value="upload_group" {if $action == 'upload_group'}selected{/if}>Für eine Gruppe hochladen</option>
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
            </select>
        </div>

        <div class="mb-3" id="custom-course-wrapper" style="display: none;">
            <label for="custom_course" class="form-label">Kursvorschlag</label>
            <input type="text" id="custom_course" name="custom_course" class="form-control" value="{$customCourse|escape}" placeholder="z. B. Informatik 1">
        </div>

        {if $userGroup}
        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" id="group_upload" name="group_upload" value="1" {if $groupUploadChecked}checked{/if}>
            <label class="form-check-label" for="group_upload">Für meine Lerngruppe hochladen</label>
        </div>
        {/if}

        <div class="mb-3">
        <input type="file" id="file" name="file" class="form-control"
            accept=".pdf,.jpg,.jpeg,.png,.txt,.doc,.docx,.odt,.ppt,.pptx" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT, PPTX Max. 10 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>

    {if $userGroups|@count > 0}
    <form id="upload-group-form" action="{$base_url}/upload.php" method="post" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="action" value="upload_group">

        <div class="mb-3">
            <label for="title_group" class="form-label">Titel</label>
            <input type="text" id="title_group" name="title" class="form-control" value="{$title|escape}">
        </div>

        <div class="mb-3">
            <label for="description_group" class="form-label">Beschreibung</label>
            <textarea id="description_group" name="description" class="form-control" rows="3">{$description|escape}</textarea>
        </div>

        <div class="mb-3">
            <label for="course_group" class="form-label">Kurs</label>
            <select id="course_group" name="course" class="form-select" onchange="toggleCustomCourseGroup(this.value)">
                <option value="" disabled {if !$selectedCourse}selected{/if}>Bitte wählen...</option>
                {foreach from=$courses item=course}
                    <option value="{$course.value|escape}" {if $course.value == $selectedCourse}selected{/if}>{$course.name|escape}</option>
                {/foreach}
            </select>
        </div>

        <div class="mb-3" id="custom-course-group-wrapper" style="display: none;">
            <label for="custom_course_group" class="form-label">Kursvorschlag</label>
            <input type="text" id="custom_course_group" name="custom_course" class="form-control" value="{$customCourse|escape}" placeholder="z. B. Informatik 1">
        </div>

        <div class="mb-3">
            <label for="group_id" class="form-label">Gruppe</label>
            <select id="group_id" name="group_id" class="form-select">
                {foreach from=$userGroups item=g}
                    <option value="{$g.id}" {if $g.id == $selectedGroupId}selected{/if}>{$g.name|escape}</option>
                {/foreach}
            </select>
        </div>

        <div class="mb-3">
            <input type="file" id="file_group" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt,.doc,.docx,.odt,.ppt,.pptx" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT, PPTX Max. 10 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>
    {/if}

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
function toggleCustomCourseGroup(value) {
    const wrapper = document.getElementById('custom-course-group-wrapper');
    if (wrapper) {
        wrapper.style.display = (value === '__custom__') ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', function () {
    toggleCustomCourse(document.getElementById('course').value);
    const cg = document.getElementById('course_group');
    if (cg) { toggleCustomCourseGroup(cg.value); }
    toggleAction(document.getElementById('action').value);
});

function toggleAction(val) {
    const uploadForm = document.getElementById('upload-form');
    const groupForm = document.getElementById('upload-group-form');
    const suggestForm = document.getElementById('suggest-form');
    if (val === 'suggest') {
        uploadForm.style.display = 'none';
        if (groupForm) groupForm.style.display = 'none';
        suggestForm.style.display = 'block';
    } else if (val === 'upload_group') {
        uploadForm.style.display = 'none';
        if (groupForm) groupForm.style.display = 'block';
        suggestForm.style.display = 'none';
    } else {
        uploadForm.style.display = 'block';
        if (groupForm) groupForm.style.display = 'none';
        suggestForm.style.display = 'none';
    }
}
</script>
{/literal}
{/block}
