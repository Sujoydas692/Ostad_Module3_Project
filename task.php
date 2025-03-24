<?php

define("TASKS_FILE", "tasks.json");

function saveTasks(array $tasks): void
{
    file_put_contents(TASKS_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

function loadTasks()
{
    if (!file_exists(TASKS_FILE)) {
        return [];
    }

    $data = file_get_contents(TASKS_FILE);

    return $data ? json_decode($data, true) : [];
}

$tasks = loadTasks();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task']) && !empty(trim($_POST['task']))) {
        $tasks[] = [
            "task" => htmlspecialchars(trim($_POST['task'])),
            "done" => false
        ];

        saveTasks($tasks);
        header('Location:' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['delete'])) {

        unset($tasks[$_POST['delete']]);
        $tasks = array_values($tasks);
        saveTasks($tasks);
        header('Location:' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['toggle'])) {
        $tasks[$_POST['toggle']]['done'] = !$tasks[$_POST['toggle']]['done'];
        saveTasks($tasks);
        header('Location:' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['update'])) {
        $tasks[$_POST['update']]['task'] = htmlspecialchars(trim($_POST['updated_task']));
        saveTasks($tasks);
        header('Location:' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.min.css">
    <style>
        body {
            margin-top: 20px;
        }

        .task-card {
            border: 1px solid #ececec;
            padding: 20px;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .task {
            color: #9b4dca;
            font-size: 1.3rem;
        }

        .task-done {
            text-decoration: line-through;
            color: #888;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        ul {
            padding-left: 20px;
        }

        button {
            cursor: pointer;
            text-transform: none;
        }

        .checkbox-container {
            display: flex;
            cursor: pointer;
            flex-grow: 1;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            background-color: #eee;
            border: 2px solid #9b4dca;
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox-container input {
            display: none;
        }

        .checkbox-container input:checked~.checkmark {
            background-color: #888;
            border: 2px solid #888;
        }

        .checkmark::after {
            content: "✔";
            color: white;
            font-size: 14px;
            display: none;
        }

        .checkbox-container input:checked~.checkmark::after {
            display: block;
        }

        .task-text {
            letter-spacing: .1rem;
            cursor: pointer;
        }

        .edit-form {
            display: none;
            margin-left: 10px;
        }

        .edit-form.active {
            display: flex;
            gap: 10px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .edit-input {
            flex-grow: 1;
            margin: 0;
        }
        
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="task-card">
            <h1 style="text-align: center; color: #9b4dca; text-transform: uppercase;">To-Do Application</h1>

            <!-- Add Task Form -->
            <form method="POST">
                <div class="row">
                    <div class="column column-75">
                        <input type="text" name="task" placeholder="Enter a new task" required>
                    </div>
                    <div class="column column-25">
                        <button type="submit" class="button-primary" style="text-transform: uppercase;">Add Task</button>
                    </div>
                </div>
            </form>

            <!-- Task List -->
            <h2>Task List</h2>
            <ul style="list-style: none; padding: 0;">
                <?php if (empty($tasks)): ?>

                    <li>No tasks yet. Add one above!</li>
                <?php else: ?>
                    <?php foreach ($tasks as $index => $task): ?>
                        <li class="task-item">
                            <form method="POST">
                                <input type="hidden" name="toggle" value="<?= $index ?>">

                                <label class="checkbox-container">
                                    <input
                                        type="checkbox"
                                        <?= $task['done'] ? 'checked' : '' ?>
                                        onchange="this.form.submit()">
                                    <span class="checkmark"></span>
                                    <span class="task-text task <?= $task['done'] ? 'task-done' : '' ?>">
                                        <?= ucwords($task['task']) ?>
                                    </span>
                                </label>

                            </form>

                            <div class="actions">
                                <button onclick="toggleEditForm(<?= $index ?>)" class="button button-outline" <?= $task['done'] ? 'disabled' : '' ?> >Edit</button>

                                <form method="POST">
                                    <input type="hidden" name="delete" value="<?= $index ?>">
                                    <button type="submit" class="button button-outline" style="margin-left: 10px;">Delete</button>
                                </form>
                            </div>

                            <!-- Edit Task Form -->
                            <form method="POST" class="edit-form" id="edit-form-<?= $index ?>">
                                <input type="hidden" name="update" value="<?= $index ?>">
                                <input type="text" name="updated_task" value="<?= ucwords($task['task']) ?>" class="edit-input" required>
                                <button type="submit" class="button button-primary">Update</button>
                            </form>


                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>


        </div>
    </div>
    <script>
        function toggleEditForm(index) {
            const editForm = document.getElementById(`edit-form-${index}`);
            editForm.classList.toggle('active');
        }
    </script>
</body>

</html>