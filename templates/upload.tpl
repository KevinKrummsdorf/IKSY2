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

    <form action="{$base_url}/upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">

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

        {if $userGroup}
        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" id="group_upload" name="group_upload" value="1" {if $groupUploadChecked}checked{/if}>
            <label class="form-check-label" for="group_upload">Für meine Lerngruppe hochladen</label>
        </div>
        {/if}

        <div class="mb-3">
            <label for="file" class="form-label">Datei auswählen</label>
            <input type="file" id="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT, DOC, DOCX, ODT, PPT Max. 10 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
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
});
</script>
{/literal}
{/block}
