<?php
    enum Role {
        case Creator;
        case Editor;
        case Reader;
    }
    class Project 
    {
        public int $Id;
        public int $Project_Id;
        public int $User_Id;
        public bool $Is_Public;
        public Role $Role;
        
        public function __construct($id, $project_id, $user_id, $is_public, Role $role)
        {
            $this->Id = $id;
            $this->Project_Id = $project_id;
            $this->User_Id = $user_id;
            $this->Is_Public = $is_public;
            $this->Role = $role;
        }
    }
?>