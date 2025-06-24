<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.inc.php';

// Initialisiere die Session-Variable für kürzlich erledigte Aufgaben
if (!isset($_SESSION['just_completed'])) {
    $_SESSION['just_completed'] = [];
}

// Sicherstellen, dass der Benutzer eingeloggt ist
if (empty($_SESSION['user_id'])) {
    $reason = urlencode("Du musst eingeloggt sein, um ToDos zu erstellen.");
    header("Location: {$config['base_url']}/error.php?code=403&reason={$reason}&action=both");
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$showDone = isset($_GET['show_done']) && $_GET['show_done'] == '1';

// Priorität eines offenen ToDos aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_priority'])) {
    $todoId  = (int)$_POST['todo_id'];
    $priority = $_POST['priority'] ?? 'medium';
    DbFunctions::updateTodoPriority($todoId, $userId, $priority);
    header('Location: todos.php');
    exit;
}

// Neues ToDo hinzufügen, inklusive Fälligkeitsdatum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_todo'])) {
    $text = trim($_POST['new_todo']);
    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $priority = $_POST['priority'] ?? 'medium';

    if ($text !== '') {
        DbFunctions::insertTodo($userId, $text, $dueDate, $priority);
    }

    // Nach dem Hinzufügen neu laden (Vermeidung von POST-Resubmits)
    header("Location: todos.php");
    exit;
}

// ToDo als erledigt oder rückgängig markieren
if (isset($_GET['toggle'])) {
    $todoId = (int)$_GET['toggle'];
    
    $todo = DbFunctions::getTodoStatus($todoId, $userId);
    
    if ($todo) {
        $newStatus = (int)!$todo['is_done'];
        DbFunctions::updateTodoStatus($todoId, $userId, $newStatus);
        
        if ($newStatus === 1) {
            $_SESSION['just_completed'][] = $todoId;
        } else {
            $_SESSION['just_completed'] = array_diff($_SESSION['just_completed'], [$todoId]);
        }
    }
    
    header("Location: todos.php");
    exit;
}

// Einzelnes erledigtes ToDo löschen
if (isset($_GET['delete'])) {
    $todoId = (int)$_GET['delete'];
    DbFunctions::deleteTodo($todoId, $userId);
    header('Location: todos.php?show_done=1');
    exit;
}

// Alle erledigten ToDos löschen
if (isset($_GET['delete_completed'])) {
    DbFunctions::deleteCompletedTodos($userId);
    header('Location: todos.php?show_done=1');
    exit;
}


// Alle ToDos des Benutzers laden (auch die bereits erledigten)
$todos = DbFunctions::getTodosByUserId($userId);

// ToDos in offene und erledigte Aufgaben aufteilen
$todos_unfinished = [];
$todos_finished = [];

foreach ($todos as $todo) {
    if ($todo['is_done']) {
        $todos_finished[] = $todo;
    } else {
        $todos_unfinished[] = $todo;
    }
}

// Für UI-Highlight von neu erledigten Aufgaben
$justCompleted = $_SESSION['just_completed'] ?? [];

// Heutiges Datum (für Prüfung auf Überfälligkeit im Template)
$today = date('Y-m-d');
$smarty->assign('today', $today);

// Daten an Smarty-Template übergeben
$smarty->assign('justCompleted', $justCompleted);
$smarty->assign('todos_unfinished', $todos_unfinished);
$smarty->assign('todos_finished', $todos_finished);
$smarty->assign('showDone', $showDone);

// Session zurücksetzen, damit Hervorhebung beim nächsten Laden nicht bleibt
unset($_SESSION['just_completed']);

// Template rendern
$smarty->display('todos.tpl');
