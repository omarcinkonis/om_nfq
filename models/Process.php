<?php
// enable access to session variables
session_start();

// Include required files
// include_once './config/Database.php';
include_once(dirname(__DIR__) . '/config/Database.php');
include_once 'Project.php';
include_once 'Student.php';

// Instantiate Process
$process = new Process();


// Send a post request to create a new project
if (isset($_GET['addProject'])) {
    $process->addProject();
}

// Delete project
if (isset($_GET['deleteProject'])) {
    $process->deleteProject();
}

// Send a post request to create a new student
if (isset($_GET['addStudent'])) {
    $process->addStudent();
}

// Delete student
if (isset($_GET['deleteStudent'])) {
    $process->deleteStudent();
}

// Assign student to a group
if (isset($_GET['assignStudent'])) {
    $process->assignStudent();
}

// Update functions
if (isset($_GET['refreshStudents'])) {
    $process->displayStudents();
}

if (isset($_GET['refreshGroups'])) {
    $process->displayGroups();
}


class Process
{
    // PDO instance
    private $conn;

    // Constructor
    public function __construct()
    {
        // Instantiate DB and connect
        $database = new Database();
        $this->conn = $database->connect();
    }


    // Functions
    // Get user input, validate it and add project
    public function addProject()
    {
        // Get user input
        $p_name = $_GET['p_name'];
        $p_numberOfGroups = $_GET['p_numberOfGroups'];
        $p_studentsPerGroup = $_GET['p_studentsPerGroup'];

        // If input meets the requirements, add project 
        if ($this->validateData($p_name, $p_numberOfGroups, $p_studentsPerGroup)) {
            // Instantiate Project
            $project = new Project($this->conn);

            // Assign user input
            $project->p_name = $p_name;
            $project->p_numberOfGroups = $p_numberOfGroups;
            $project->p_studentsPerGroup = $p_studentsPerGroup;

            // Create project
            $project->create();

            // Set response
            $_SESSION['message'] = "Project added!";
        }

        // Redirect
        header('location: /om_nfq-master/index.php');
    }

    // Get user input, validate it and add student
    public function addStudent()
    {
        // Get project id
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        } else return false;

        // Get student name
        $s_name = $_GET['s_name'];

        // If input meets the requirements, add student 
        if ($this->validateData($s_name)) {
            // use REST to add student
            $this->postStudent($s_name, $id);
        }

        // Redirect
        $link = 'location: /om_nfq-master/projectStatus.php?id=' . $id;
        header($link);
    }

    // Validate user input when adding a project or a student
    private function validateData($name, $p_numberOfGroups = null, $p_studentsPerGroup = null)
    {
        // Validate project/student name
        if (!isset($name) || $name == "") {
            $_SESSION['message'] = "ERROR: Name not given.";
            return false;
        } else if (strlen($name) > 255) {
            $_SESSION['message'] = "ERROR: Given name exceeds 255 character limit.";
            return false;
        }

        // Validate number of groups
        if (isset($p_numberOfGroups) && ($p_numberOfGroups < 2 || $p_numberOfGroups > 100)) {
            $_SESSION['message'] = "ERROR: Given number of groups does not meet given requirement (from 2 to 100)";
            return false;
        }

        // Validate students per group
        if (isset($p_studentsPerGroup) && ($p_studentsPerGroup < 2 || $p_studentsPerGroup > 100)) {
            $_SESSION['message'] = "ERROR: Given number of students per group does not meet given requirement (from 2 to 100)";
            return false;
        }

        return true;
    }

    public function assignStudent()
    {
        // Get project id
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        } else return false;

        // Instantiate student object
        $student = new Student($this->conn, $id);

        // Get details and update
        $s_id = $_GET['s_id'];
        $g_number = $_GET['g_number'];
        $student->update($s_id, $g_number);
    }

    public function deleteProject()
    {
        // Get p_id
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        } else return false;

        // Instantiate Project and Student
        $project = new Project($this->conn);
        $student = new Student($this->conn, $id);

        // Project query
        $result = $student->read();

        // Get row count
        $num = $result->rowCount();

        // If participants exist in p_s_junction, remove them
        if ($num > 0) {
            while ($row = $result->fetch()) {
                $student->remove($row['s_name']);
            }
        }

        // Get project id, delete project
        $p_id = $_GET['p_id'];
        $project->delete($p_id);

        // Generate response
        $_SESSION['message'] = "Project deleted!";
        header('location: /om_nfq-master/index.php');
    }

    public function deleteStudent()
    {
        // Get p_id
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        } else return false;

        // Instantiate student object
        $student = new Student($this->conn, $id);

        // Get details and remove
        $s_name = $_GET['s_name'];
        $student->remove($s_name);

        // Redirect
        $link = 'location: /om_nfq-master/projectStatus.php?id=' . $id;
        header($link);
    }

    # Display
    // Display projects in a table
    public function displayProjects()
    {
        // Instantiate Project
        $project = new Project($this->conn);

        // Project query
        $result = $project->read();

        echo '
            <table>
            <tr>
                <th class="setWidth">Project</th>
                <th>Number of groups</th>
                <th>Students per group</th>
                <th>Created at</th>
                <th>Actions</th>
            </tr>
        ';
        // Read and display projects
        while ($row = $result->fetch()) : ?>
            <tr>
                <td class="setWidth">
                    <a href="projectStatus.php?id=<?= $row['p_id']; ?>" class="truncate" title="<?= $row['p_name']; ?>">
                        <?= $row['p_name']; ?>
                    </a>
                </td>

                <td><?= $row['p_numberOfGroups']; ?></td>

                <td><?= $row['p_studentsPerGroup']; ?></td>

                <td><?= substr($row['p_createdAt'], 0, strrpos($row['p_createdAt'], ' ')); // extract date from datetime 
                    ?></td>

                <td><a href="http://localhost/om_nfq-master/models/Process.php?p_id=<?= $row['p_id']; ?>&deleteProject=1">Delete</a></td>
            </tr>
        <?php endwhile;
        echo '</table>';
    }

    public function displayProjectInfo($p_id)
    {
        $project = new Project($this->conn);
        $project->readSingle($p_id);
        echo '
            <p>Project: <b>' . $project->p_name . '</b></p>
            <p>Number of groups: <b>' . $project->p_numberOfGroups . '</b></p>
            <p>Students per group: <b>' . $project->p_studentsPerGroup . '</b></p>
        ';
    }

    // Display project groups in tables
    public function displayGroups($id = null)
    {
        // Instantiate project
        $project = new Project($this->conn);

        // Project query
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        }
        $project->readSingle($id);

        // Enable the use of required Student functions that return students participating in this project
        include_once(dirname(__DIR__) . '/models/Student.php');
        $student = new Student($this->conn, $project->p_id);

        // Get info about students participating in the project
        $notInGroups = $student->read('notInGroups');
        $choices = $notInGroups->fetchAll(); // fetch according to the default mode

        $inGroups = $student->read('inGroups');
        $row = $inGroups->fetch();

        // Used to give a number to each <select>. The number is later used to deselect <select> elements after clicking on them twice (not done automatically by browser, prevents refresh)
        $selectNo = 0;

        // Fetch and display participants in groups
        for ($i = 0; $i < $project->p_numberOfGroups; $i++) : ?>
            <table class="groupTable">
                <tr>
                    <th>Group #<?= $i + 1 ?></th>
                </tr>
                <?php for ($j = 0; $j < $project->p_studentsPerGroup; $j++) : ?>
                    <tr>
                        <td>
                            <?php
                            $row['g_number'] ??= null; // if $row['g_number'] is not set, gives it a null value
                            if ($row['g_number'] == $i + 1) : // check if participant belongs to the group that is currently being displayed 
                                echo ('<span class="truncate" title="' . $row['s_name'] . '">' . $row['s_name'] . '</span>'); // echo student name if their group number matches current group number
                                $row = $inGroups->fetch(); // fetch the next student assigned to a group
                            else : ?>
                                <select onclick="deselect(<?= ++$selectNo ?>)">
                                    <option onclick="refresh()">Select student</option>

                                <?php
                                foreach ($choices as $choice) {
                                    echo '<option onclick="updateStudent(' . $choice['s_id'] . ', ' . ($i + 1) . ')">' . $choice['s_name'] . '</option>';
                                }
                            endif; ?>
                                </select>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
        <?php endfor;
    }

    // Display students from the current project in a table
    public function displayStudents($id = null)
    {
        // Instantiate project
        $project = new Project($this->conn);

        // Project query
        if (isset($_GET['p_id'])) {
            $id = $_GET['p_id'];
        }
        $project->readSingle($id);

        // Enable the use of required Student functions that return students participating in this project
        include_once(dirname(__DIR__) . '/models/Student.php');
        $student = new Student($this->conn, $project->p_id);

        echo '
            <table>
            <tr>
                <th class="setWidth">Student</th>
                <th class="setWidth">Group</th>
                <th>Actions</th>
            </tr>
        ';

        // Participant query
        $result = $student->read();

        // Fetch and display participants
        while ($row = $result->fetch()) : ?>
            <tr>
                <td class="setWidth"><span title="<?= $row['s_name']; ?>" class="truncate"> <?= $row['s_name']; ?></span></td>
                <td class="setWidth">
                    <?php
                    if (isset($row['g_number'])) {
                        echo "Group #" . $row['g_number'];
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td><a href="http://localhost/om_nfq-master/models/Process.php?s_name=<?= $row['s_name']; ?>&p_id=<?= $project->p_id; ?>&deleteStudent=1">Delete</a></td>
            </tr>
        <?php
        endwhile;
        echo '</table>';
    }

    # REST
    private function postStudent($s_name, $p_id)
    {
        $url = 'http://localhost/om_nfq-master/api/student/create.php';
        $data = array('s_name' => $s_name, 'p_id' => $p_id);

        // always use key "http", even for "https""
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result == '{"message":"Failed to create student"}') { // Handle error
            $_SESSION['message'] = "ERROR: Unable to add student. Ensure that the specified student is not included in this project already.";
        } else {
            $_SESSION['message'] = "Student added!";
        }
    }
}
