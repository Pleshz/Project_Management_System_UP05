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
            $sql = "SELECT * FROM `projects`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);
            
            while ($row = $result->fetch_assoc()) {
                $allProjects[] = new ProjectContext($row);
            }

            Connection::closeConnection($connection);
            return $allProjects;
       }
    }
?>