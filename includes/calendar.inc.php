<?php
declare(strict_types=1);

use Smarty\Smarty;

/**
 * Prepares a monthly calendar with user tasks and assigns it to Smarty.
 */
function assignUserCalendarToSmarty(PDO $pdo, Smarty $smarty): void
{
    if (empty($_SESSION['user_id'])) {
        return; // no user context
    }

    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    if ($month < 1 || $month > 12) {
        $month = (int)date('n');
    }
    if ($year < 1970 || $year > 2100) {
        $year = (int)date('Y');
    }

    $current = DateTimeImmutable::createFromFormat('!Y-n-d', sprintf('%04d-%02d-01', $year, $month));
    if (!$current) {
        $current = new DateTimeImmutable('first day of this month');
    }

    $daysInMonth = (int)$current->format('t');

    $startDate = $current->format('Y-m-01');
    $endDate   = $current->format('Y-m-' . str_pad((string)$daysInMonth, 2, '0', STR_PAD_LEFT));

    $rows = DbFunctions::getTodosForDateRange(
        (int)$_SESSION['user_id'],
        $startDate,
        $endDate
    );

    $tasksByDay = [];
    foreach ($rows as $row) {
        $day = (int)substr($row['due_date'], 8, 2);
        $tasksByDay[$day][] = [
            'title' => $row['title'],
            'priority' => $row['priority'],
        ];
    }

    $calendar = [];
    $week = [];
    $firstWeekday = (int)$current->format('N'); // 1 (Mon) - 7 (Sun)
    for ($i = 1; $i < $firstWeekday; $i++) {
        $week[] = null;
    }

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $week[] = [
            'day' => $day,
            'tasks' => $tasksByDay[$day] ?? [],
        ];
        if (count($week) === 7) {
            $calendar[] = $week;
            $week = [];
        }
    }
    if ($week) {
        while (count($week) < 7) {
            $week[] = null;
        }
        $calendar[] = $week;
    }

    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter(
            'de_DE',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            'Europe/Berlin',
            IntlDateFormatter::GREGORIAN,
            'LLLL yyyy'
        );
        $monthLabel = $fmt->format($current);
    } else {
        $monthLabel = strftime('%B %Y', $current->getTimestamp());
    }

    $prev  = $current->modify('-1 month');
    $next  = $current->modify('+1 month');
    $today = new DateTimeImmutable('today');

    $smarty->assign('calendar', $calendar);
    $smarty->assign('currentMonthLabel', $monthLabel);
    $smarty->assign('nav', [
        'prev_month'  => (int)$prev->format('n'),
        'prev_year'   => (int)$prev->format('Y'),
        'next_month'  => (int)$next->format('n'),
        'next_year'   => (int)$next->format('Y'),
        'today_month' => (int)$today->format('n'),
        'today_year'  => (int)$today->format('Y'),
        'current_month' => (int)$month,
        'current_year'  => (int)$year,
    ]);
}

/**
 * Loads today's tasks for the logged in user and assigns them to Smarty.
 */
function assignTodayTodosToSmarty(PDO $pdo, Smarty $smarty): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $today = (new DateTimeImmutable('today'))->format('Y-m-d');

    $todos = DbFunctions::getTodosForDateRange(
        (int)$_SESSION['user_id'],
        $today,
        $today
    );

    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter(
            'de_DE',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Europe/Berlin',
            IntlDateFormatter::GREGORIAN
        );
        $todayLabel = $fmt->format(new DateTimeImmutable('today'));
    } else {
        $todayLabel = strftime('%A, %e. %B %Y');
    }

    $smarty->assign('todayLabel', $todayLabel);
    $smarty->assign('todayTodos', $todos);
}
