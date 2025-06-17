{extends file="./layouts/layout.tpl"}

{block name="title"}Dein Stundenplan{/block}

{block name="content"}

{if $success}
    <p style="text-align:center; color:green; font-weight:bold;">✔ Stundenplan gespeichert!</p>
{/if}

<h2 style="text-align: center; margin-bottom: 20px;">Stundenplan</h2>

<style>
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
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

<form method="post" action="timetable.php">
    <table class="timetable-table">
        <thead>
            <tr>
                <th>Zeit</th>
                {foreach $days as $day}
                    <th>{$day|capitalize}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {foreach $timeSlots as $idx => $slotRange}
                <tr>
                    <td>{$slotRange}</td>
                    {foreach $days as $day}
                        {assign var="entry" value=$timetable[$day][$idx]|default:null}
                        <td>
                            <input type="text"
                                   name="timetable[{$day}][{$idx}][fach]"
                                   value="{$entry.subject|default:''|escape:'html'}"
                                   placeholder="Subject" /><br>
                            <input type="text"
                                   name="timetable[{$day}][{$idx}][raum]"
                                   value="{$entry.room|default:''|escape:'html'}"
                                   placeholder="Room" />
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </tbody>
    </table>

    <button type="submit" class="submit-button">Speichern</button>
</form>

{/block}
