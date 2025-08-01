<div class="today-tasks my-4">
  <h2 class="mb-3">Aufgaben für {$todayLabel|escape:'html'}</h2>
  {if $todayTodos|@count > 0}
    <ul class="list-group">
      {foreach $todayTodos as $task}
        {if $task.is_group_event|default:false}
          <li class="list-group-item list-group-item-info d-flex align-items-center">
            <img src="{if $task.group_picture}{url file="group_pictures/{$task.group_picture|escape:'url'}"}{else}{$base_url}/assets/default_group.png{/if}"
                 alt="Gruppenbild" class="me-1 rounded-circle" style="width:24px;height:24px;object-fit:cover;">
            {$task.title|escape:'html'}
            {if $task.event_time}
              <span class="ms-2 text-muted small">{$task.event_time|date_format:"%H:%M"} Uhr</span>
            {/if}
            <span class="ms-2 text-muted small">{$task.group_name|escape:'html'}</span>
          </li>
        {else}
          {assign var="bg" value="#d4edda"}
          {if $task.priority == 'medium'}{assign var="bg" value="#fff3cd"}{/if}
          {if $task.priority == 'high'}{assign var="bg" value="#f8d7da"}{/if}
          <li class="list-group-item" style="background-color: {$bg};">
            {$task.title|escape:'html'}
          </li>
        {/if}
      {/foreach}
    </ul>
  {else}
    <div class="alert alert-info">Keine Aufgaben für heute.</div>
  {/if}
</div>

