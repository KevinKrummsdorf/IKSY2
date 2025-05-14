{extends file='layout.tpl'} {block name="title"}Material
hochladen{/block} {block name="content"}
<div class="container">
	<h1 class="mb-4 text-center">Material hochladen</h1>
	{if isset($error)}
	<div class="alert alert-danger">{$error}</div>
	{/if} {if isset($success)}
	<div class="alert alert-success">{$success}</div>
	{/if}

	<form action="{$base_url}/upload.php" method="post"
		enctype="multipart/form-data">
		<div class="mb-3">
			<label for="title" class="form-label">Titel</label> <input
				type="text" id="title" name="title" class="form-control"
				value="{$title|escape}" required>
		</div>
		<div class="mb-3">
			<label for="description" class="form-label">Beschreibung</label>
			<textarea id="description" name="description" class="form-control"
				rows="3">{$description|escape}</textarea>
		</div>
		<div class="mb-3">
			<label for="course" class="form-label">Kurs</label> <select
				id="course" name="course" class="form-select"> {foreach
				from=$courses item=course}
				<option value="{$course.value|escape}"
					{if $course.value==$selectedCourse}selected{/if}>{$course.name|escape}</option>
				{/foreach}
			</select>
		</div>
		<div class="mb-3">
			<label for="file" class="form-label">Datei auswählen</label> <input
				type="file" id="file" name="file" class="form-control" required>
		</div>
		<button type="submit" class="btn btn-primary">Hochladen</button>
	</form>
</div>
{/block}
