<?php
    require_once __DIR__.'/../models/task.php';
    require_once __DIR__.'/common/connection.php';
    
    class TaskContext extends Tasks
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'],
                name: $data['name'],
                description: $data['description'] ?? null,
                due_date: isset($data['due_date']) ? (new DateTime($data['due_date'])) : null,
                column_id: $data['column_id']
            );
        }
    
        public static function select(): array
        {
            $allTasks = [];
            $sql = "SELECT * FROM `Tasks`;";
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
            $sql = "INSERT INTO `Tasks` (`name`, `description`, `due_date`, `column_id`) VALUES (?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $name = $this->Name;
            $description = $this->Description;
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $column_id = $this->Column_Id;
            $stmt->bind_param("sssi", $name, $description, $due_date_str, $column_id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `Tasks` SET `name` = ?, `description` = ?, `due_date` = ?, `column_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $name = $this->Name;
            $description = $this->Description;
            $due_date_str = $this->Due_Date ? $this->Due_Date->format('Y-m-d H:i:s') : null;
            $column_id = $this->Column_Id;
            $id = $this->Id;
            $stmt->bind_param("sssii", $name, $description, $due_date_str, $column_id, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `Tasks` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>