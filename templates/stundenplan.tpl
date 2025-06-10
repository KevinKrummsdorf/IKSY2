{extends file="./layouts/layout.tpl"}

{block name="title"}Dein Stundenplan{/block}

{block name="content"}

{if $success}
    <p style="text-align:center; color:green; font-weight:bold;">✔ Stundenplan gespeichert!</p>
{/if}

<h2 style="text-align: center; margin-bottom: 20px;">Stundenplan</h2>

<style>
    .stundenplan-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        font-family: Arial, sans-serif;
        background-color: rgba(255, 255, 255, 0.03);
        border: 2px solid #444;
    }

    .stundenplan-table th,
    .stundenplan-table td {
        border: 1px solid #444;
        padding: 8px;
        vertical-align: top;
        text-align: center;
    }

    .stundenplan-table th {
        background-color: rgba(240, 240, 240, 0.6);
    }

    .stundenplan-table tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .stundenplan-table input[type="text"] {
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

    .stundenplan-table input[type="text"]:focus {
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

{assign var="tage" value=["montag","dienstag","mittwoch","donnerstag","freitag"]}

<form method="post" action="">
    <table class="stundenplan-table">
        <thead>
            <tr>
                <th>Zeit</th>
                {foreach from=$tage item=tag}
                    <th>{$tag|capitalize}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {section name=row loop=10}
            <tr>
                <td>
                    <input type="text" name="zeit[{$smarty.section.row.index}]" 
                           value="{$stundenplan.montag[$smarty.section.row.index].zeit|default:''}" 
                           placeholder="z. B. 08:00 - 08:45" />
                </td>
                {foreach from=$tage item=tag}
                <td>
                    <input type="text" name="stundenplan[{$tag}][{$smarty.section.row.index}][fach]" 
                           value="{$stundenplan[$tag][$smarty.section.row.index].fach|default:''}" 
                           placeholder="Fach" /><br>
                    <input type="text" name="stundenplan[{$tag}][{$smarty.section.row.index}][raum]" 
                           value="{$stundenplan[$tag][$smarty.section.row.index].raum|default:''}" 
                           placeholder="Raum" />
                </td>
                {/foreach}
            </tr>
            {/section}
        </tbody>
    </table>

    <button type="submit" class="submit-button">Speichern</button>
</form>

{/block}
