<?php

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function testStudentDeleteReturnsTrueOrFalse()
    {
        // Include required classes
        include_once 'config/Database.php';
        include_once 'models/Project.php';
        include_once 'models/Student.php';

        // Instantiate Database and connect
        $database = new Database();
        $db = $database->connect();

        // Instantiate Project
        $project = new Project($db);

        // Create a new project
        $project->p_name = "Delete student unit test";
        $project->p_numberOfGroups = "2";
        $project->p_studentsPerGroup = "2";
        $project->create();

        // Get ID of newly created project
        $result = $project->read();
        $row = $result->fetch();
        $project->p_id = $row['p_id'];

        // Instantiate Student
        $student = new Student($db, $project->p_id);

        // Create 2 students in current project
        $student->create("Student to delete 1");
        $student->create("Student to delete 2");

        // Try to delete 1st student, assert true
        $this->assertTrue(
            $student->remove("Student to delete 1")
        );

        // Try to delete 2nd student, assert true
        $this->assertTrue(
            $student->remove("Student to delete 2")
        );

        // Try to delete a non-existing student, assert false
        $this->assertFalse(
            $student->remove("Non-existing student")
        );

        // Delete the project that was created for this test
        $project->delete($project->p_id);
    }
}
