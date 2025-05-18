<?php
    require_once __DIR__.'/../models/subtask.php';
    require_once __DIR__.'/common/connection.php';
    
    class SubtaskContext extends Subtask
    {
        public function __construct(array $data)
        {
            parent::__construct(
                $data['id'],
                $data['task_id'],
                $data['name'],
                $data['description'] ?? null,
                isset($data['due_date']) ? (new DateTime($data['due_date'])) : null,
                $data['column_id'],
                $data['assignee_id'] ?? null
            );
        }
    
        public static function select(): array
        {
            $allSubtasks = [];
            $sql = "SELECT * FROM `subtasks`;";
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
            $sql = "INSERT INTO `subtasks` (`task_id`, `name`, `description`, `due_date`, `column_id`, `assignee_id`) VALUES (?, ?, ?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $stmt->bind_param("isssii",
                $this->Task_Id,
                $this->Name,
                $this->Description,
                $due_date_str,
                $this->Column_Id,
                $this->Assignee_Id
            );
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `subtasks` SET `task_id` = ?, `name` = ?, `description` = ?, `due_date` = ?, `column_id` = ?, `assignee_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $stmt->bind_param("isssiis",
                $this->Task_Id,
                $this->Name,
                $this->Description,
                $due_date_str,
                $this->Column_Id,
                $this->Assignee_Id,
                $this->Id
            );
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `subtasks` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>