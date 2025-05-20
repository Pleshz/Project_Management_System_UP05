<?php
    enum Role {
        case Creator;
        case Editor;
        case Reader;

        public static function fromString(string $role): self {
            return match(strtolower($role)) {
                'creator' => self::Creator,
                'editor' => self::Editor,
                'reader' => self::Reader,
                default => throw new InvalidArgumentException("Unknown role: $role"),
            };
        }
    }
    class ProjectUser 
    {
        public int $Id;
        public int $Project_Id;
        public int $User_Id;
        public Role $Role;
        
        public function __construct($id, $project_id, $user_id, Role $role)
        {
            $this->Id = $id;
            $this->Project_Id = $project_id;
            $this->User_Id = $user_id;
            $this->Role = $role;
        }
    }
?>