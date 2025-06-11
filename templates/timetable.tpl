{extends file="./layouts/layout.tpl"}

{block name="title"}Your Timetable{/block}

{block name="content"}

{if $success}
    <p style="text-align:center; color:green; font-weight:bold;">✔ Timetable saved!</p>
{/if}

<h2 style="text-align: center; margin-bottom: 20px;">Timetable</h2>

<style>
    .timetable-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        font-family: Arial, sans-serif;
        background-color: rgba(255, 255, 255, 0.03);
        border: 2px solid #444;
    }

    .timetable-table th,
    .timetable-table td {
        border: 1px solid #444;
        padding: 8px;
        vertical-align: top;
        text-align: center;
    }

    .timetable-table th {
        background-color: rgba(240, 240, 240, 0.6);
    }

    .timetable-table tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .timetable-table input[type="text"] {
        width: 100%;
        padding: 6px;
        box-sizing: border-box;
        font-size: 14px;
        border: none;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        color: #000;
        text-align: center;
    }

    .timetable-table input[type="text"]:focus {
        background-color: rgba(255, 255, 255, 0.2);
        outline: none;
    }

    .submit-button {
        margin-top: 20px;
        display: block;
        width: 200px;
        padding: 10px;
        font-size: 16px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-left: auto;
        margin-right: auto;
    }

    .submit-button:hover {
        background-color: #0056b3;
    }
</style>

{assign var="days" value=["montag","dienstag","mittwoch","donnerstag","freitag"]}

<form method="post" action="timetable.php">
    <table class="timetable-table">
        <thead>
            <tr>
                <th>Time</th>
                {foreach from=$days item=day}
                    <th>{$day|capitalize}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {section name=row loop=10}
            <tr>
                <td>
                    <input type="text" name="time[{$smarty.section.row.index}]" 
                           value="{$timetable.montag[$smarty.section.row.index].time|default:''}" 
                           placeholder="e.g. 08:00 - 08:45" />
                </td>
                {foreach from=$days item=day}
                <td>
                    <input type="text" name="timetable[{$day}][{$smarty.section.row.index}][fach]" 
                           value="{$timetable[$day][$smarty.section.row.index].subject|default:''}" 
                           placeholder="Subject" /><br>
                    <input type="text" name="timetable[{$day}][{$smarty.section.row.index}][raum]" 
                           value="{$timetable[$day][$smarty.section.row.index].room|default:''}" 
                           placeholder="Room" />
                </td>
                {/foreach}
            </tr>
            {/section}
        </tbody>
    </table>

    <button type="submit" class="submit-button">Save</button>
</form>

{/block}
