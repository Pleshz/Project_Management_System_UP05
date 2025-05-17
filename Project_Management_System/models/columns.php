<?php
    class Project 
    {
        public int $Id;
        public string $Name;
        public int $Project_Id;
        
        public function __construct($id, $name, $project_id)
        {
            $this->Id = $id;
            $this->Name = $name;
            $this->Project_Id = $project_id;
        }
    }
?>