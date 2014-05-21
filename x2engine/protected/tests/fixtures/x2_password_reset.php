<?php
$now = time();
return array(
    '1' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000000',
        'ip' => '127.0.0.1',
        'requested'=> $now,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '2' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000001',
        'ip' => '127.0.0.1',
        'requested'=> $now,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '3' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000002',
        'ip' => '127.0.0.1',
        'requested'=> $now,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '4' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000003',
        'ip' => '127.0.0.1',
        'requested'=> $now,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '5' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000004',
        'ip' => '127.0.0.1',
        'requested'=> $now,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '6' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000005',
        'ip' => '127.0.0.1',
        'requested'=> $now-PasswordReset::EXPIRE_S-1,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '7' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000006',
        'ip' => '127.0.0.1',
        'requested'=> $now-PasswordReset::EXPIRE_S-1,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '8' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000007',
        'ip' => '127.0.0.1',
        'requested'=> $now-PasswordReset::EXPIRE_S-1,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '9' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000008',
        'ip' => '127.0.0.1',
        'requested'=> $now-PasswordReset::EXPIRE_S-1,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),
    '10' => array(
        'id'=>'0000000000000000000000000000000000000000000000000000000000000009',
        'ip' => '127.0.0.1',
        'requested'=> $now-PasswordReset::EXPIRE_S-1,
        'email' => 'sales@rep.com',
        'userId' => 2
    ),

);