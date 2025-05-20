<?php
    require_once __DIR__.'/../models/project.php';
    require_once __DIR__.'/common/connection.php';
    class ProjectContext extends Project 
    {
       public function __construct(array $data)
       {
            parent::__construct(
                id: $data['id'],
                name: $data['name'],
                description: $data['description'] ?? null,
                is_public: $data['is_public'],
            );
       } 

       public static function select():array
       {
            $allProjects = [];
            $sql = "SELECT * FROM `Projects`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);
            
            while ($row = $result->fetch_assoc()) {
                $allProjects[] = new ProjectContext($row);
            }

            Connection::closeConnection($connection);
            return $allProjects;
       }

       public function add(): void
        {
            $sql = "INSERT INTO `Projects` (`name`, `description`, `is_public`) VALUES (?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $name = $this->Name;
            $description = $this->Description;
            $is_public = $this->Is_Public;
               
            $stmt->bind_param("ssb", $name, $description, $is_public);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function update(): void
        {
            $sql = "UPDATE `Projects` SET `name` = ?, `description` = ?, `is_public` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $name = $this->Name;
            $description = $this->Description;
            $is_public = $this->Is_Public;
            $id = $this->Id;
            $stmt->bind_param("ssbi", $name, $description, $is_public, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function delete(): void
        {
            $sql = "DELETE FROM `Projects` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>