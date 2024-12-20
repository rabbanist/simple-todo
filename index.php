<?php 

define("TASKS_FILE", "tasks.json");

// Fetch all tasks from the JSON file
function getTasks() {
    if (!file_exists(TASKS_FILE)) {
        return [];
    }

    $tasks = file_get_contents(TASKS_FILE);
    return json_decode($tasks, true);
}

// Save tasks to the JSON file
function saveTasks($tasks): void {
    file_put_contents(TASKS_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Add a new task
function addTask($task) {
    $tasks = getTasks();
    $tasks[] = [
        'task' => trim(htmlspecialchars($task)),
        "done" => false
    ];
    saveTasks($tasks);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Delete a task by ID
function deleteTask($id) {
    $tasks = getTasks();
    unset($tasks[$id]);
    saveTasks(array_values($tasks));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Toggle the status of a task
function toggleTaskStatus($id) {
    $tasks = getTasks();
    if (isset($tasks[$id])) {
        $tasks[$id]["done"] = !$tasks[$id]["done"];
        saveTasks($tasks);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Validate task input
function validateTask($task) {
    if (empty($task)) {
        return "Task cannot be empty";
    }
    return null;
}

$errorMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["task"])) {
        $task = trim($_POST["task"]);
        $errorMessage = validateTask($task);
        if (!$errorMessage) {
            addTask($task);
        }
    } elseif (isset($_POST["delete_id"])) {
        deleteTask($_POST["delete_id"]);
    } elseif (isset($_POST["toggle_id"])) {
        toggleTaskStatus($_POST["toggle_id"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Todo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.css">
    <style>
        .container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 800px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .completed {
            text-decoration: line-through;
            color: gray;
        }
        .task-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
        }
        .task-name {
            flex: 1;
            cursor: pointer;
        }
        .task-actions {
            display: flex;
            gap: 10px;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Simple Todo List</h1>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="text" name="task" class="full-width" placeholder="What do you need to do?">
                <?php if (!empty($errorMessage)): ?>
                    <div class="error-message"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
                <button type="submit" class="button-primary">Add Task</button>
            </form>

            <h2>Current Tasks</h2>
            <hr>
            <ul>
                <?php if (empty(getTasks())): ?>
                    <li>No tasks found</li>
                <?php else: ?>
                    <?php foreach (getTasks() as $id => $task): ?>
                        <li class="task-row">
                            <span 
                                class="task-name <?php echo $task['done'] ? 'completed' : ''; ?>" 
                                onclick="document.getElementById('toggle-form-<?php echo $id; ?>').submit();">
                                <?php echo $task['task']; ?>
                            </span>
                            <div class="task-actions">
                                <form id="toggle-form-<?php echo $id; ?>" action="" method="post" style="display:none;">
                                    <input type="hidden" name="toggle_id" value="<?php echo $id; ?>">
                                </form>
                                <form action="" method="post">
                                    <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                                    <button class="button" type="submit">Delete</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
