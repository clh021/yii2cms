<?php
//phpinfo();
//die();
// var_dump(defined('SAE_MYSQL_HOST_M'));
// $link=mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
// if(!$link) echo "FAILD!连接错误，用户名密码不对"; 
// else echo "OK!可以连接"; 
$sae = defined('SAE_MYSQL_HOST_M');
$db = $sae ? [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.SAE_MYSQL_HOST_M.';port='.SAE_MYSQL_PORT.';dbname='.SAE_MYSQL_DB,
    'username' => SAE_MYSQL_USER,
    'password' => SAE_MYSQL_PASS,
    'charset' => 'utf8',
] : [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=mysql;dbname=yii2advanced',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
];
include('./vendor/yiisoft/yii2/Yii.php');
$connection = new \yii\db\Connection([
    'dsn' => $db['dsn'],
    'username' => $db['username'],
    'password' => $db['password'],
]);
$connection->open();
$command = $connection->createCommand('SELECT * FROM user');
$posts = $command->queryAll();
var_dump($posts);
// $directory = __DIR__;
// $filePath = 'vendor';
// echo $directory.'/'.$filePath;

// $mydir = dir($filePath);
// echo "<ul>\n";
// while($file = $mydir->read())
// {
//    if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!=".."))
//    {
//        echo "<li><font color=\"#ff00cc\"><b>$file</b></font></li>\n";
//    } else {
//        $f = @read_exif_data($filePath,null,true);
//        var_dump($f);
//    }
// }
// echo "</ul>\n";
// $mydir->close();