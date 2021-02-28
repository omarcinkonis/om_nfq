<?php

class Student
{
    // PDO instance
    private $conn;

    // Student properties
    private $p_id;
    public $s_id;
    public $s_name;
    public $g_number;

    // Constructor with DB and Project ID
    public function __construct($db, $p_id)
    {
        $this->conn = $db;
        $this->p_id = $p_id;
    }

    # CRUD
    // Create student
    public function create($s_name)
    {
        $_SESSION['message'] = "TEST";
        // Create Student if it doesn't already exist in database
        if (!$this->readSingle($s_name)) {
            // Create query
            $query = '
                INSERT INTO student
                SET s_name = :s_name
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Clean data
            $s_name = htmlspecialchars(strip_tags($s_name));

            // Bind data
            $stmt->bindParam(':s_name', $s_name);

            // Execute query
            if ($stmt->execute()) {
                // Assign student to Project
                $this->readSingle($s_name);
                $this->assignToProject();
                return true;
            }

            // Print error if something goes wrong
            printf("ERROR: ", $stmt->error);

            return false;
        } else { // Check if student already belongs to this project
            if ($this->readSingle($s_name, "current")) {
                return false;
            } else { // Assign Student to project if the Student already exists in the database but is not assigned
                $this->assignToProject();
                return true;
            }
        }
    }

    // Function used by $this->create() to assign created student to the current project
    private function assignToProject()
    {
        // Create query
        $query = '
        INSERT INTO p_s_junction
        SET
            p_id = :p_id,
            s_id = :s_id
        ';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind data
        $stmt->bindParam(':p_id', $this->p_id);
        $stmt->bindParam(':s_id', $this->s_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /* Get students participating in current project
     * Accepted parameters:
     *  No parameter (default - null) - returns participants from current project
     *  "inGroups" - returns participants assigned to groups (from current project)
     *  "notInGroups" - returns participants which aren't assigned to groups (from current project)
     *  "inGroup" - returns participants assigned to a specific (from current project); requires additional parameter $g_number
    */
    public function read($param = null, $g_number = null)
    {
        if (
            $param == "inGroups"
        ) {
            // Create query
            $query = '
                SELECT s_id, s_name, g_number
                FROM p_s_junction
                NATURAL JOIN student
                WHERE p_id = ?
                AND g_number IS NOT NULL
                ORDER BY g_number
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Bind ID and execute query
            $stmt->execute([$this->p_id]);
        } else if (
            $param == "notInGroups"
        ) {
            // Create query
            $query = '
                SELECT s_id, s_name, g_number
                FROM p_s_junction
                NATURAL JOIN student
                WHERE p_id = ?
                AND g_number IS NULL
                ORDER BY g_number
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Bind ID and execute query
            $stmt->execute([$this->p_id]);
        } else if ($param == "inGroup") {
            // Create query
            $query = '
                SELECT s_id, s_name, g_number
                FROM p_s_junction
                NATURAL JOIN student
                WHERE p_id = :p_id
                AND g_number = :g_number
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Clean data
            $g_number = htmlspecialchars(strip_tags($g_number));

            // Bind parameters
            $stmt->bindParam(':p_id', $this->p_id);
            $stmt->bindParam(':g_number', $g_number);

            // Execute query
            $stmt->execute();
        } else {
            // Create query
            $query = '
                SELECT s_id, s_name, g_number
                FROM p_s_junction
                NATURAL JOIN student
                WHERE p_id = ?
                ORDER BY s_name
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Bind ID and execute query
            $stmt->execute([$this->p_id]);
        }

        return $stmt;
    }

    /* Get one student
     * Accepted parameters:
     *  null (default) - returns student with specified name
     *  "currentById" - returns student with specified id (instead of name), participating in the CURRENT project
     *  "current" - returns student with specified name, participating in the CURRENT project
     *  "any" - returns student with specified name, participating in ANY (AT LEAST ONE) project
    */
    public function readSingle($name, $param = null)
    {
        if ($param == "currentById") {
            $s_id = $name;

            // Select with id instead of name
            $query = '
                SELECT student.s_id, s_name, g_number
                FROM student
                LEFT JOIN p_s_junction
                ON student.s_id = p_s_junction.s_id
                WHERE student.s_id = :s_id
                AND p_id = :p_id
            ';

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Clean data
            $s_id = htmlspecialchars(strip_tags($s_id));

            // Bind parameters
            $stmt->bindParam(':s_id', $s_id);
            $stmt->bindParam(':p_id', $this->p_id);
        } else {
            // Create query
            $query = '
                SELECT student.s_id, s_name, g_number
                FROM student
                LEFT JOIN p_s_junction
                ON student.s_id = p_s_junction.s_id
                WHERE s_name = :s_name
            ';
            if ($param == "current") {
                $query = $query . ' AND p_id = :p_id';
            }
            if ($param == "any") {
                $query = $query . ' AND p_id IS NOT NULL';
            }

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Clean data
            $name = htmlspecialchars(strip_tags($name));

            // Bind parameters
            $stmt->bindParam(':s_name', $name);
            if ($param == "current") {
                $stmt->bindParam(':p_id', $this->p_id);
            }
        }

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // Ensure that student exists
        if ($num > 0) {
            $row = $stmt->fetch();

            // Set Student properties
            $this->s_id = $row['s_id'];
            $this->s_name = $row['s_name'];
            $this->g_number = $row['g_number'];

            return true;
        }

        return false;
    }

    // Assign student to a group
    public function update($s_id, $g_number)
    {
        // Alert the user if chosen student does not belong to the project
        if (!$this->readSingle($s_id, "currentById")) {
            echo "ERROR: Student could not be found in this project.";
            return false;
        }


        // Alert the user if the chosen group is full
        // Get all students from specified group
        $result = $this->read("inGroup", $g_number);

        // Get row count
        $num = $result->rowCount();

        // Instantiate project, get info about the current project
        $project = new Project($this->conn);
        $project->readSingle($this->p_id);

        // If the number of rows exceeds or matches allowed students per group, end function and inform user
        if ($num >= $project->p_studentsPerGroup) {
            echo "ERROR: The chosen group is full.";
            return false;
        }


        // Create query
        $query = '
            UPDATE p_s_junction
            SET g_number = :g_number
            WHERE p_id = :p_id
            AND s_id = :s_id
        ';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->author = htmlspecialchars(strip_tags($s_id));
        $this->title = htmlspecialchars(strip_tags($g_number));

        // Bind data
        $stmt->bindParam(':p_id', $this->p_id);
        $stmt->bindParam(':s_id', $s_id);
        $stmt->bindParam(':g_number', $g_number);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("ERROR: ", $stmt->error);

        return false;
    }

    // Remove student from project
    public function remove($s_name)
    {
        // Get full student details
        $this->readSingle($s_name);

        // Delete student from p_s_junction
        $query = '
            DELETE FROM p_s_junction
            WHERE p_id = :p_id 
            AND s_id = :s_id
        ';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind data
        $stmt->bindParam(':p_id', $this->p_id);
        $stmt->bindParam(':s_id', $this->s_id);

        // Execute query, return false if query doesn't execute
        if (!$stmt->execute()) return false; // Query executing with 0 rows affected won't return false, therefore we need to check for that separately

        // Check rows affected, if none, show error
        if ($stmt->rowCount() == 0) {
            $_SESSION['message'] = "Could not delete student because the specified student was not found.";
            return false;
        }

        // Print error if something goes wrong
        // printf("ERROR: ", $stmt->error);

        // Set session message if successful
        $_SESSION['message'] = "Student removed from the project!";

        // If student is no longer participating in any projects, delete from database
        if (!$this->readSingle($this->s_name, "any")) {
            $this->delete($this->s_id);

            // Change message
            $_SESSION['message'] = "Student deleted!";
        }

        return true;
    }

    // Delete student from database if the student no longer participates in any projects (called by $this->remove())
    private function delete($s_id)
    {
        // Delete student
        $query = 'DELETE FROM student WHERE s_id = :s_id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $s_id = htmlspecialchars(strip_tags($s_id));

        // Bind data
        $stmt->bindParam(':s_id', $s_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("ERROR: ", $stmt->error);

        return false;
    }
}
