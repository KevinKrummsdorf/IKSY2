{extends file="./layouts/layout.tpl"}

{block name="title"}Meine ToDo-Liste{/block}

{block name="content"}
<div class="container my-5">
    <h1 class="mb-4">Meine ToDo-Liste</h1>

    {*Formular zum Hinzufügen eines neuen ToDos.
        Optional kann ein Fälligkeitsdatum mitgegeben werden.*}
    <form method="post" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="new_todo" class="form-control" placeholder="Neues ToDo..." required>
            </div>
            <div class="col-md-3">
                <input type="date" name="due_date" class="form-control" placeholder="Fälligkeitsdatum">
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="high">Hoch</option>
                    <option value="medium" selected>Mittel</option>
                    <option value="low">Niedrig</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">+</button>
            </div>
        </div>
    </form>

 
    <h2>Offene Aufgaben</h2>
    {if $todos_unfinished|@count > 0}
        <ul class="list-group mb-5">
            {foreach $todos_unfinished as $todo}
                {assign var="isOverdue" value=($todo.due_date && $todo.due_date < $today)}
                <li class="list-group-item d-flex justify-content-between align-items-center {if $isOverdue}bg-danger bg-opacity-10{/if}">

                    <div>
                        <strong>{$todo.text|escape}</strong>
                        {if $todo.due_date}
                            <div class="text-muted small">
                                Fällig: {$todo.due_date|date_format:"%d.%m.%Y"}
                            </div>
                        {/if}
                        <span class="badge ms-2 {if $todo.priority=='high'}bg-danger{elseif $todo.priority=='medium'}bg-warning text-dark{else}bg-secondary{/if}">
                            {if $todo.priority=='high'}Hoch{elseif $todo.priority=='medium'}Mittel{else}Niedrig{/if}
                        </span>
                    </div>

                    {* Link zum Togglen des Status (erledigt) *}
                    <a href="?toggle={$todo.id}&show_done={$showDone ? 1 : 0}" class="btn btn-sm btn-outline-success">
                        Erledigt
                    </a>
                </li>
            {/foreach}
        </ul>
    {else}
        <p>Keine offenen Aufgaben.</p>
    {/if}

   
    <h2>Erledigte Aufgaben</h2>

    {* Umschaltknopf für Sichtbarkeit erledigter Aufgaben *}
    <form method="get" class="mb-3">
        <input type="hidden" name="show_done" value="{if $showDone}0{else}1{/if}">
        <button type="submit" class="btn btn-secondary">
            {if $showDone}
                Erledigte ausblenden
            {else}
                Alle erledigten anzeigen
            {/if}
        </button>
    </form>

    {if $showDone && $todos_finished|@count > 0}
        <form method="get" class="mb-3">
            <input type="hidden" name="delete_completed" value="1">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Alle erledigten ToDos wirklich löschen?');">
                Alle erledigten löschen
            </button>
        </form>
    {/if}

    {if $showDone}
        {if $todos_finished|@count > 0}
            <ul class="list-group">
                {foreach $todos_finished as $todo}
                    <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-success">

                        <div>
                            <span class="text-decoration-line-through">{$todo.text|escape}</span>
                            {if $todo.due_date}
                                <div class="text-muted small">
                                    Fällig: {$todo.due_date|date_format:"%d.%m.%Y"}
                                </div>
                            {/if}
                            <span class="badge ms-2 {if $todo.priority=='high'}bg-danger{elseif $todo.priority=='medium'}bg-warning text-dark{else}bg-secondary{/if}">
                                {if $todo.priority=='high'}Hoch{elseif $todo.priority=='medium'}Mittel{else}Niedrig{/if}
                            </span>
                        </div>

                        {* Rückgängig-Link (erneutes Togglen auf "nicht erledigt") *}
                        <div class="d-flex gap-2">
                            <a href="?toggle={$todo.id}&show_done=1" class="btn btn-sm btn-outline-danger">
                                Rückgängig
                            </a>
                            <a href="?delete={$todo.id}&show_done=1" class="btn btn-sm btn-outline-secondary" onclick="return confirm('ToDo wirklich löschen?');">
                                Löschen
                            </a>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {else}
            <p>Keine erledigten Aufgaben.</p>
        {/if}
    {else}
        <p class="text-muted">Erledigte Aufgaben werden derzeit ausgeblendet.</p>
    {/if}
</div>
{/block}
