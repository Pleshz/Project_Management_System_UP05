<?php
    class Connection
    {
        private static $server = "localhost";
        private static $database = "Project_Management_System";
        private static $username = "root";
        private static $password = "";

        public static function openConnection(): mysqli {
            $connection = new mysqli(hostname: self::$server, username: self::$username, password: self::$password, database: self::$database);
        
            if ($connection->connect_error) {
                die("Connection failed: " . $connection->connect_error);
            }
        
            return $connection;
        }
    
        public static function query($sql, $connection): mixed {
            $result = $connection->query($sql);
        
            if (!$result) {
                die("Query failed: " . $connection->error);
            }
        
            return $result;
        }
    
        public static function closeConnection($connection): void {
            $connection->close();
        }
    }
?>