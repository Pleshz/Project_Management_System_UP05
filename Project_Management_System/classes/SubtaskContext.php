<?php
    require_once __DIR__.'/../models/subtask.php';
    require_once __DIR__.'/common/connection.php';
    
    class SubtaskContext extends Subtask
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'],
                task_id: $data['task_id'],
                name: $data['name'],
                description: $data['description'] ?? null,
                due_date: isset($data['due_date']) ? (new DateTime($data['due_date'])) : null,
                column_id: $data['column_id'],
                assignee_id: $data['assignee_id'] ?? null
            );
        }
    
        public static function select(): array
        {
            $allSubtasks = [];
            $sql = "SELECT * FROM `Subtasks`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);

            while ($row = $result->fetch_assoc()) {
                $allSubtasks[] = new SubtaskContext($row);
            }
            Connection::closeConnection($connection);
            return $allSubtasks;
        }
    
        public function add(): void
        {
            $sql = "INSERT INTO `Subtasks` (`task_id`, `name`, `description`, `due_date`, `column_id`, `assignee_id`) VALUES (?, ?, ?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $task_id = $this->Task_Id;
            $name = $this->Name;
            $description = $this->Description;
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $column_id = $this->Column_Id;
            $assignee_id = $this->Assignee_Id;
            $stmt->bind_param("isssii", $task_id, $name, $description, $due_date_str, $column_id, $assignee_id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `Subtasks` SET `task_id` = ?, `name` = ?, `description` = ?, `due_date` = ?, `column_id` = ?, `assignee_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $task_id = $this->Task_Id;
            $name = $this->Name;
            $description = $this->Description;
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $column_id = $this->Column_Id;
            $assignee_id = $this->Assignee_Id;
            $id= $this->Id;
            $stmt->bind_param("isssiii", $task_id, $name, $description, $due_date_str, $column_id, $assignee_id, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `Subtasks` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>