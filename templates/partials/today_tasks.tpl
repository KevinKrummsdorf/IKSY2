<div class="today-tasks my-4">
  <h2 class="mb-3">Aufgaben für {$todayLabel|escape:'html'}</h2>
  {if $todayTodos|@count > 0}
    <ul class="list-group">
      {foreach $todayTodos as $task}
        {assign var="bg" value="#d4edda"}
        {if $task.priority == 'medium'}{assign var="bg" value="#fff3cd"}{/if}
        {if $task.priority == 'high'}{assign var="bg" value="#f8d7da"}{/if}
        <li class="list-group-item" style="background-color: {$bg};">
          {$task.title|escape:'html'}
        </li>
      {/foreach}
    </ul>
  {else}
    <div class="alert alert-info">Keine Aufgaben für heute.</div>
  {/if}
</div>

