<?php
// Get ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    die("ERROR: Project ID not found");
}

// Include Process (Process instantiates itself with variable $process)
include_once 'models/Process.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Status page</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div id="sideMenu">
        <a href="index.php"><img src="images/home.svg" title="Return to Home" class="svgButton"></a>
        <img src="images/refresh.svg" title="Refresh data" class="svgButton" onclick="refresh();"></a>
        <br>
        <div id="timer"></div>
    </div>

    <?= $process->displayProjectInfo($id); ?>

    <div class="tableCaption">Students</div>

    <div id="participants">
        <?php $process->displayStudents($id); ?>
    </div>

    <div class="button" onclick="dialogOn()">Add new Student</div>

    <div class="tableCaption">Groups</div>

    <div id="groups">
        <?php $process->displayGroups($id); ?>
    </div>

    <div id="overlay">
        <form id="insertForm" action="models/Process.php" method="get">
            <div class="inputCaption"> Add new student </div>

            <div>
                Student name
                <span class="requirement">(<=255 chars)</span>
            </div>
            <input type="text" id="name" name="s_name" oninput="truncate(this.value)" autocomplete="off" required>

            <input type="hidden" name="p_id" value="<?= $id ?>">

            <input type="submit" class="inputButton" value="Add student" name="addStudent">
            <input type="button" class="inputButton" value="Cancel" onclick="dialogOff()">
        </form>
    </div>
</body>

<script>
    <?php
    if (isset($_SESSION['message'])) {
        echo "alert('" . $_SESSION['message'] . "');";
        unset($_SESSION['message']);
    }
    ?>

    function updateStudent(s_id, g_number) {
        httpRequest = new XMLHttpRequest();
        if (!httpRequest) {
            alert("ERROR: Failed to send request.");
            return false;
        }
        // when httpRequest.onreadystatechange attribute is equal to function checkRequest, checkRequest() is called every time when httpRequest.onreadystatechange is used
        httpRequest.onreadystatechange = checkRequest;
        let page = "models/Process.php";
        let variables = "?p_id=" + <?= $id ?> + "&s_id=" + s_id + "&g_number=" + g_number + "&assignStudent=1";

        let url = page + variables;
        httpRequest.open('GET', url);
        httpRequest.send();
    }

    // check if request is successful
    function checkRequest() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === 200) {
                // if response is not null, display it through alert()
                if (!!httpRequest.responseText) alert(httpRequest.responseText);
                refresh();
            } else {
                alert("ERROR: Failed to complete request.");
            }
        }
    }

    // update student and group tables every 10s
    let timer = document.getElementById('timer');
    let time = 9; // start countdown from 9 and count to 0 instead of 1 for prettier alignment
    let rotated = false;
    timer.innerHTML = time;
    setInterval(function() {
        if (time > 0) {
            time--;
            timer.innerHTML = time;
        } else {
            if (selectActive()) {
                timer.innerHTML = "Complete selection to refresh data";
                timer.classList.add('fixed');
            } else {
                refresh();
            }
        }
    }, 1000);

    // check if any select elements are currently active
    function selectActive() {
        if (document.activeElement == "[object HTMLSelectElement]") return true;
        else return false;
    }

    function refresh() {
        timer.classList.remove('fixed');
        time = 9;
        timer.innerHTML = time;
        refreshStudents();
    }

    let participants = document.getElementById('participants');
    let groups = document.getElementById('groups');

    function refreshStudents() {
        httpRequest = new XMLHttpRequest();
        if (!httpRequest) {
            alert("ERROR: Failed to request for participants.");
            return false;
        }

        httpRequest.onreadystatechange = loadParticipants;
        let url = "models/Process.php?p_id=<?= $id ?>&refreshStudents=1";

        httpRequest.open('GET', url);
        httpRequest.send();
    }

    function loadParticipants() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === 200) {
                participants.innerHTML = httpRequest.responseText;
                refreshGroups();
            } else {
                alert("ERROR: Failed to load participants.");
                refreshGroups();
            }
        }
    }

    function refreshGroups() {
        httpRequest = new XMLHttpRequest();
        if (!httpRequest) {
            alert("ERROR: Failed to request for groups.");
            return false;
        }

        httpRequest.onreadystatechange = loadGroups;
        let url = "models/Process.php?p_id=<?= $id ?>&refreshGroups=1";

        httpRequest.open('GET', url);
        httpRequest.send();
    }

    function loadGroups() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === 200) {
                groups.innerHTML = httpRequest.responseText;
            } else {
                alert("ERROR: Failed to load groups.");
            }
        }
    }

    // Clicking a <select> twice doesn't automatically deselect it. This prevents the data from reloading. To walk around this questionable browser behavior, we deselect with Javascript
    let selected = 0;

    function deselect(selection) {
        if (selected == selection) {
            document.activeElement.blur();
            selected = 0;
        } else selected = selection;
    }
</script>
<script src="script.js"></script>

</html>