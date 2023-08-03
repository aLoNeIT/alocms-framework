<?php

$config = [
    'password' => '123456',
    'algorithm' => 'md5'
];

function ttt($pwd)
{
    var_dump($pwd);
}

if (is_callable($config['algorithm'])) {
    $result = call_user_func($config['algorithm'], $config['password']);
    var_dump($result);
}
