<?php
    class Project 
    {
        public int $Id;
        public string $Name;
        public ?string $Description;
        public bool $Is_Public;
        
        public function __construct($id, $name, $description = null, $is_public)
        {
            $this->Id = $id;
            $this->Name = $name;
            $this->Description = $description;
            $this->Is_Public = $is_public;
        }
    }
?>