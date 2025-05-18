<?php
    require_once __DIR__.'/../models/task.php';
    require_once __DIR__.'/common/connection.php';
    
    class TaskContext extends Tasks
    {
        public function __construct(array $data)
        {
            parent::__construct(
                $data['id'],
                $data['name'],
                $data['description'] ?? null,
                isset($data['due_date']) ? (new DateTime($data['due_date'])) : null,
                $data['column_id']
            );
        }
    
        public static function select(): array
        {
            $allTasks = [];
            $sql = "SELECT * FROM `tasks`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);
            while ($row = $result->fetch_assoc()) {
                $allTasks[] = new TaskContext($row);
            }
            Connection::closeConnection($connection);
            return $allTasks;
        }
    
        public function add(): void
        {
            $sql = "INSERT INTO `tasks` (`name`, `description`, `due_date`, `column_id`) VALUES (?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $stmt->bind_param("sssi", $this->Name, $this->Description, $due_date_str, $this->Column_Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `tasks` SET `name` = ?, `description` = ?, `due_date` = ?, `column_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $stmt->bind_param("sssii", $this->Name, $this->Description, $due_date_str, $this->Column_Id, $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `tasks` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>