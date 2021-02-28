<?php
// Include Process (Process instantiates itself with variable $process)
include_once 'models/Process.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="tableCaption">Projects</div>

    <?php $process->displayProjects(); ?>

    <div class="button" onclick="dialogOn()">Add new project</div>

    <div id="overlay">
        <form id="insertForm" action="models/Process.php" method="get">
            <div class="inputCaption"> Add new project </div>

            <div>
                Project name
                <span class="requirement">(<=255 chars)</span>
            </div>
            <input type="text" id="name" name="p_name" oninput="truncate(this.value)" autocomplete="off" required>

            <div>
                Number of groups
                <span class="requirement">(2 .. 100)</span>
            </div>
            <input type="number" min="2" max="100" name="p_numberOfGroups" required>

            <div>
                Students per group
                <span class="requirement">(2 .. 100)</span>
            </div>
            <input type="number" min="2" max="100" name="p_studentsPerGroup" required>

            <input type="submit" class="inputButton" value="Add project" name="addProject">
            <input type="button" class="inputButton" value="Cancel" onclick="dialogOff()">
        </form>
    </div>

    <script>
        <?php
        if (isset($_SESSION['message'])) {
            echo "alert('" . $_SESSION['message'] . "');";
            unset($_SESSION['message']);
        }
        ?>
    </script>
    <script src="script.js"></script>
</body>

</html>