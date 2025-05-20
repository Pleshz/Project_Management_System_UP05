<?php
    require_once __DIR__ . "/classes/UserContext.php";
    

    $list = array(
        'id' => 1,
        'login' => 'plesh',
        'email' => 'pleskovd95@gmail.com',
        'password' => 'admin123',
        'language' => 'ru',
        'full_name' => null,
        'bio' => null
    );
    
    $obj = new UserContext($list);
    $obj->update();
    // $users = $obj->select();
    
    // foreach($users as $user)
    // {
    //     echo "Id = " . $user->Id . " login = " . $user->Login . " password = " . $user->Password . " language = " . $user->Language->name;
    // }
?>