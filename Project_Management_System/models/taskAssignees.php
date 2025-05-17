<?php
    class TaskAssignees 
    {
        public int $Id;
        public int $Task_Id;
        public int $User_Id;
        
        public function __construct($id, $task_id, $user_id)
        {
            $this->Id = $id;
            $this->Task_Id = $task_id;
            $this->User_Id = $user_id;
        }
    }
?>