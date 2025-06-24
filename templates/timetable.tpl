{extends file="./layouts/layout.tpl"}

{block name="title"}Dein Stundenplan{/block}

{block name="content"}

{if $success}
    <p class="timetable-success-message">✔ Stundenplan gespeichert!</p>
{/if}

<h2 class="timetable-heading">Stundenplan</h2>

<form method="post" action="{url path='timetable'}">
    <table class="timetable-table">
        <thead>
            <tr>
                <th>Zeit</th>
                {foreach $weekdays as $day}
                    <th>{$day.day_name|capitalize|escape}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {foreach $timeSlots as $slot}
                <tr>
                    <td>{$slot.start_time|date_format:"%H:%M"} - {$slot.end_time|date_format:"%H:%M"}</td>
                    {foreach $weekdays as $day}
                        {assign var="entry" value=$timetable[$day.id][$slot.id]|default:null}
                        <td>
                            <input type="text"
                                   name="timetable[{$day.id}][{$slot.id}][fach]"
                                   value="{$entry.subject|default:''|escape}"
                                   placeholder="Fach"
                                   class="timetable-input timetable-input--small subject-input"
                                   list="course-list" autocomplete="off" /><br>
                            <input type="text"
                                   name="timetable[{$day.id}][{$slot.id}][raum]"
                                   value="{$entry.room|default:''|escape}"
                                   placeholder="Raum"
                                   class="timetable-input timetable-input--small" />
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </tbody>
    </table>

    <datalist id="course-list"></datalist>

    <button type="submit" class="submit-button">Speichern</button>
</form>

<div class="mt-3 text-center">
    <a href="{$base_url}/timetable?export=csv" class="download-link me-2">
        <span class="material-symbols-outlined">download</span>
        <span>CSV</span>
        <small>Stundenplan</small>
    </a>
    <a href="{$base_url}/timetable?export=pdf" class="download-link">
        <span class="material-symbols-outlined">picture_as_pdf</span>
        <span>PDF</span>
        <small>Stundenplan</small>
    </a>
</div>

<script src="{$base_url}/js/timetable-autocomplete.js"></script>

{/block}

