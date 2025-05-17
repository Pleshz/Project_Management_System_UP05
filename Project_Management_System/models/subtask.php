<?php
    class Subtask 
    {
        public int $Id;
        public int $Task_Id;
        public string $Name;
        public ?string $Description;
        public ?DateTime $Due_Date;
        public int $Column_Id;
        public ?int $Assignee_Id;
        
        public function __construct($id, $task_id, $name, $description, $due_date, $column_id, $assignee_id)
        {
            $this->Id = $id;
            $this->Task_Id = $task_id;
            $this->Name = $name;
            $this->Description = $description;
            $this->Due_Date = $due_date;
            $this->Column_Id = $column_id;
            $this->Assignee_Id = $assignee_id;
        }
    }
?>