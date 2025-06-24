{extends file="./layouts/layout.tpl"}

{block name="title"}Dein Stundenplan{/block}

{block name="content"}

{if $success}
    <p class="timetable-success-message">✔ Stundenplan gespeichert!</p>
{/if}

<h2 class="timetable-heading">Stundenplan</h2>

<form method="post" action="timetable.php">
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
                                   class="timetable-input timetable-input--small" /><br>
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

    <button type="submit" class="submit-button">Speichern</button>
</form>


{/block}

