<div class="calendar-container my-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h4 mb-0">{$currentMonthLabel|escape:'html'}</h2>
    <div>
      <a class="btn btn-outline-primary btn-sm me-1" href="?month={$nav.prev_month|escape:'url'}&year={$nav.prev_year|escape:'url'}">&laquo; Vorheriger Monat</a>
      <a class="btn btn-outline-secondary btn-sm me-1" href="?month={$nav.today_month|escape:'url'}&year={$nav.today_year|escape:'url'}">Heute anzeigen</a>
      <a class="btn btn-outline-primary btn-sm" href="?month={$nav.next_month|escape:'url'}&year={$nav.next_year|escape:'url'}">Nächster Monat &raquo;</a>
    </div>
  </div>

  <div class="row g-0 border text-center fw-bold bg-light small">
    <div class="col border p-1">Mo</div>
    <div class="col border p-1">Di</div>
    <div class="col border p-1">Mi</div>
    <div class="col border p-1">Do</div>
    <div class="col border p-1">Fr</div>
    <div class="col border p-1">Sa</div>
    <div class="col border p-1">So</div>
  </div>

  {foreach $calendar as $week}
    <div class="row g-0">
      {foreach $week as $day}
        {if $day}
          <div class="col calendar-cell{if $day.is_today} calendar-today{/if}">
            <span class="calendar-date">{$day.day}</span>
            {foreach $day.tasks as $task}
              {if $task.is_group_event}
                <div class="calendar-task calendar-group-event">
                  <img src="{if $task.group_picture}{url file="group_pictures/{$task.group_picture|escape:'url'}"}{else}{$base_url}/assets/default_group.png{/if}"
                       alt="Gruppenbild" style="width:16px;height:16px;object-fit:cover;border-radius:50%;">
                  {$task.title|escape:'html'}
                </div>
              {else}
                {assign var="bg" value="#d4edda"}
                {if $task.priority == 'medium'}{assign var="bg" value="#fff3cd"}{/if}
                {if $task.priority == 'high'}{assign var="bg" value="#f8d7da"}{/if}
                <div class="calendar-task" style="background-color: {$bg};">
                  {$task.title|escape:'html'}
                </div>
              {/if}
            {/foreach}
          </div>
        {else}
          <div class="col calendar-cell"></div>
        {/if}
      {/foreach}
    </div>
  {/foreach}
</div>

