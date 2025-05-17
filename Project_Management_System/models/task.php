<?php
    class Tasks 
    {
        public int $Id;
        public string $Name;
        public ?string $Description;
        public ?DateTime $Due_Date;
        public int $Column_Id;
        
        public function __construct($id, $name, $description = null, $due_date = null, $column_id)
        {
            $this->Id = $id;
            $this->Name = $name;
            $this->Description = $description;
            $this->Due_Date = $due_date;
            $this->Column_Id = $column_id;
        }
    }
?>