{extends file="./layouts/layout.tpl"}

{block name="title"}Dein Stundenplan{/block}

{block name="content"}

{if $success}
    <p class="timetable-success-message">✔ Stundenplan gespeichert!</p>
{/if}

<h2 class="timetable-heading">Stundenplan</h2>

<form method="post" action="{url path='timetable'}" class="mt-3">
    <div class="table-responsive">
    <table class="table table-bordered align-middle timetable-table">
        <thead>
            <tr>
                <th class="text-break">Zeit</th>
                {foreach $weekdays as $day}
                    <th class="text-break">{$day.day_name|capitalize|escape}</th>
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
                                   class="form-control form-control-sm timetable-input timetable-input--small subject-input"
                                   list="course-list" autocomplete="off" /><br>
                            <input type="text"
                                   name="timetable[{$day.id}][{$slot.id}][raum]"
                                   value="{$entry.room|default:''|escape}"
                                   placeholder="Raum"
                                   class="form-control form-control-sm timetable-input timetable-input--small" />
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </tbody>
    </table>
    </div>

    <datalist id="course-list"></datalist>

    <div class="text-center">
        <button type="submit" class="btn btn-primary submit-button w-100 w-md-auto">Speichern</button>
    </div>
</form>

<div class="mt-3 text-center">
    <a href="{url path='timetable' export='csv'}" class="download-link me-2">
        <span class="material-symbols-outlined">download</span>
        <span>CSV</span>
        <small>Stundenplan</small>
    </a>
    <a href="{url path='timetable' export='pdf'}" class="download-link">
        <span class="material-symbols-outlined">picture_as_pdf</span>
        <span>PDF</span>
        <small>Stundenplan</small>
    </a>
</div>

<script src="{$base_url}/js/timetable-autocomplete.js"></script>

{/block}

