<div class="calendar-container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">{$currentMonthLabel|escape:'html'}</h2>
    <div>
      <a class="btn btn-outline-primary btn-sm me-1" href="?month={$nav.prev_month|escape:'url'}&year={$nav.prev_year|escape:'url'}">&laquo; Vorheriger Monat</a>
      <a class="btn btn-outline-secondary btn-sm me-1" href="?month={$nav.today_month|escape:'url'}&year={$nav.today_year|escape:'url'}">Heute anzeigen</a>
      <a class="btn btn-outline-primary btn-sm" href="?month={$nav.next_month|escape:'url'}&year={$nav.next_year|escape:'url'}">Nächster Monat &raquo;</a>
    </div>
  </div>

  <div class="row border text-center fw-bold bg-light">
    <div class="col border p-1">Mo</div>
    <div class="col border p-1">Di</div>
    <div class="col border p-1">Mi</div>
    <div class="col border p-1">Do</div>
    <div class="col border p-1">Fr</div>
    <div class="col border p-1">Sa</div>
    <div class="col border p-1">So</div>
  </div>

  {foreach $calendar as $week}
    <div class="row">
      {foreach $week as $day}
        {if $day}
          <div class="col border p-2" style="min-height: 8rem;">
            <div class="fw-bold">{$day.day}</div>
            {foreach $day.tasks as $task}
              {assign var="bg" value="#d4edda"}
              {if $task.priority == 'medium'}{assign var="bg" value="#fff3cd"}{/if}
              {if $task.priority == 'high'}{assign var="bg" value="#f8d7da"}{/if}
              <div class="p-1 mb-1" style="background-color: {$bg};">
                {$task.title|escape:'html'}
              </div>
            {/foreach}
          </div>
        {else}
          <div class="col border p-2" style="min-height: 8rem;"></div>
        {/if}
      {/foreach}
    </div>
  {/foreach}
</div>

