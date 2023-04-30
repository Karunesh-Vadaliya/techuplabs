<?php

class TaskController
{
    private $db;
    private $validStatus;
    private $validPriority;

    public function __construct()
    {
        $this->db = new Database();
        $this->validStatus = ['New', 'Incomplete', 'Complete'];
        $this->validPriority = ['High', 'Medium', 'Low'];
    }

    public function create($data, $files)
    {
        // Validate input data
        $subject = $data['subject'];
        $description = $data['description'];
        $startDate = $data['start_date'];
        $dueDate = $data['due_date'];
        $status = $data['status'];
        $priority = $data['priority'];

        // Validate subject
        if (!preg_match("/^[a-zA-Z0-9 '-]{2,50}$/", $subject)) {
            apiResponse(400, "Invalid subject");
        }
        // Validate start date
        if (!DateTime::createFromFormat('Y-m-d', $startDate)) {
            apiResponse(400, 'Invalid start date');
        }
        // Validate due date
        if (!DateTime::createFromFormat('Y-m-d', $dueDate)) {
            apiResponse(400, 'Invalid due date');
        }
        // Check if the status values is valid
        if (!in_array($status, $this->validStatus)) {
            apiResponse(400, "Invalid status value");
        }
        // Check if the priority values is valid
        if (!in_array($priority, $this->validPriority)) {
            apiResponse(400, "Invalid priority value");
        }

        if (!empty($data['notes'])) {
            $notes = $data['notes'];
            foreach ($notes as $key => $value) {
                if (empty($value['subject'])) {
                    apiResponse(400, "Notes " . ($key + 1) . "of subject is required.");
                }
            }
        }

        // Create new task
        $taskId = $this->db->insert("tasks", array(
            "user_id" => $GLOBALS['user_id'],
            "subject" => $subject,
            "description" => $description,
            "start_date" => $startDate,
            "due_date" => $dueDate,
            "status" => $status,
            "priority" => $priority
        ));
        if ($taskId > 0) {
            if (!empty($data['notes'])) {
                $notes = $data['notes'];
                $notesIds = [];
                foreach ($notes as $key => $value) {
                    // Create notes
                    $noteId = $this->db->insert("notes", array(
                        "task_id" => $taskId,
                        "subject" => $value['subject'],
                        "note" => $value['note']
                    ));
                    if ($noteId > 0) {
                        array_push($notesIds, $noteId);
                        foreach ($files['notes']['tmp_name'][$key]['attachment'] as $k1 => $v2) {
                            $pathParts = pathinfo($files['notes']['name'][$key]['attachment'][$k1]);
                            $fileName = $GLOBALS['user_id'] . time() . rand(1111, 9999) . "." . $pathParts['extension'];
                            $fineName = $files['notes']['name'][$key]['attachment'][$k1];
                            $filePath = 'uploads/' . $fileName;
                            // Create attachment
                            $attachmentId = $this->db->insert("attachments", array(
                                "note_id" => $noteId,
                                "filename" => $fineName,
                                "filepath" => $filePath
                            ));
                            if ($attachmentId > 0) {
                                if (!move_uploaded_file($v2, 'uploads/' . $fileName)) {
                                    $this->rollback($taskId, $notesIds);
                                    apiResponse(400, 'Error: Notes not created');
                                }
                            } else {
                                $this->rollback($taskId, $notesIds);
                                apiResponse(400, 'Error: Notes not created');
                            }
                        }
                    } else {
                        $this->rollback($taskId, $notesIds);
                        apiResponse(400, 'Error: Notes not created');
                    }
                }
            }
            apiResponse(200, 'Task created successfully');
        } else {
            apiResponse(400, 'Error: Task not created');
        }
    }

    public function list($data)
    {
        $tasksQuery = 'SELECT tasks.*, COUNT(n.id) AS total_notes FROM tasks
            LEFT JOIN notes n ON tasks.id = n.task_id
            WHERE tasks.user_id = ' . $GLOBALS['user_id'];
        if (isset($data['filter'])) {
            $filter = $data['filter'];

            $status = $filter['status'] ?? null;
            $priority = $filter['priority'] ?? null;
            $dueDate = $filter['due_date'] ?? null;
            $notes = $filter['notes'] ?? "All";

            // Validate due date
            if (!DateTime::createFromFormat('Y-m-d', $dueDate) && $dueDate) {
                apiResponse(400, 'Invalid due date');
            }

            if ($status) {
                // Check if the status values is valid
                if (!in_array($status, $this->validStatus)) {
                    apiResponse(400, "Invalid status value");
                }
                $tasksQuery .= " AND status = '" . $status . "' ";
            }
            if ($priority) {
                // Check if the priority values is valid
                if (!in_array($priority, $this->validPriority)) {
                    apiResponse(400, "Invalid priority value");
                }
                $tasksQuery .= " AND priority = '" . $priority . "' ";
            }
            if ($dueDate) {
                $tasksQuery .= " AND dueDate = '" . $dueDate . "' ";
            }
            if ($notes == 'Yes') {
                $tasksQuery .= " HAVING COUNT(total_notes) > 0 ";
            }
            if ($notes == 'No') {
                $tasksQuery .= " HAVING COUNT(total_notes) <= 0 ";
            }
        }
        $tasksQuery .= " ORDER BY FIELD(tasks.priority, 'High', 'Medium', 'Low'), total_notes DESC";
        $taskData = $this->db->getTask($tasksQuery);
        foreach ($taskData as $key => $taskRow) {
            $notesQuery = 'SELECT * FROM notes WHERE task_id = ' . $taskRow['id'];
            $notesData = $this->db->getTask($notesQuery);
            foreach ($notesData as $key => $noteRow) {
                $attachmentsQuery = 'SELECT *, CONCAT("' . BASE_URL . '/", filepath) as attachment_link FROM attachments WHERE note_id = ' . $noteRow['id'];
                $notesData[$key]['attachments'] = $this->db->getTask($attachmentsQuery);
            }
            $taskData[$key]['notes'] = $notesData;
        }
        apiResponse(200, "", $taskData);
    }

    private function rollback($taskId, $notesId = [])
    {
        $this->db->delete("attachments", "note_id IN " . $notesId);
        $this->db->delete("notes", "id IN " . $notesId);
        $this->db->delete("tasks", "id = " . $taskId);
    }
}
