<?php
class Project
{
    // PDO instance
    private $conn;

    // Project properties
    public $p_id;
    public $p_name;
    public $p_numberOfGroups;
    public $p_studentsPerGroup;
    public $p_createdAt;

    // Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    # CRUD FUNCTIONS
    // Create Project
    public function create()
    {
        // Create query
        $query = '
        INSERT INTO project
        SET
            p_name = :p_name,
            p_numberOfGroups = :p_numberOfGroups,
            p_studentsPerGroup = :p_studentsPerGroup
        ';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->p_name = htmlspecialchars(strip_tags($this->p_name));
        $this->p_numberOfGroups = htmlspecialchars(strip_tags($this->p_numberOfGroups));
        $this->p_studentsPerGroup = htmlspecialchars(strip_tags($this->p_studentsPerGroup));

        // Bind data
        $stmt->bindParam(':p_name', $this->p_name);
        $stmt->bindParam(':p_numberOfGroups', $this->p_numberOfGroups);
        $stmt->bindParam(':p_studentsPerGroup', $this->p_studentsPerGroup);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("ERROR: ", $stmt->error);

        return false;
    }

    // Get Projects
    public function read()
    {
        // Create query
        $query = '
            SELECT p_id, p_name, p_numberOfGroups, p_studentsPerGroup, p_createdAt
            FROM project
            ORDER BY p_createdAt DESC
        ';

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Execute statement
        $stmt->execute();

        return $stmt;
    }

    // Get one project
    public function readSingle($id)
    {
        // Create query
        $query = '
            SELECT p_id, p_name, p_numberOfGroups, p_studentsPerGroup, p_createdAt
            FROM project
            WHERE p_id = ?
        ';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $id = htmlspecialchars(strip_tags($id));

        // Bind ID and execute query
        $stmt->execute([$id]);

        // Get row count
        $num = $stmt->rowCount();

        // Ensure that project exists
        if ($num < 1) die("ERROR: Requested project does not exist");

        $row = $stmt->fetch();

        // Set properties
        $this->p_id = $row['p_id'];
        $this->p_name = $row['p_name'];
        $this->p_numberOfGroups = $row['p_numberOfGroups'];
        $this->p_studentsPerGroup = $row['p_studentsPerGroup'];
        $this->p_createdAt = $row['p_createdAt'];
    }

    // Delete Project
    public function delete($p_id)
    {
        // Create query
        $query = 'DELETE FROM project WHERE p_id = :p_id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $p_id = htmlspecialchars(strip_tags($p_id));

        // Bind data
        $stmt->bindParam(':p_id', $p_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("ERROR: ", $stmt->error);

        return false;
    }
}
