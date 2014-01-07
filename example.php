<?php

require_once 'src/Crystal.php';

use Crystal\main as Crystal;

Crystal::setTable('users');
$DB = new Crystal();

//Insert
$insert = $DB->insert(
    ['nick' => rand(), 'password' => rand(), "country" => "argentina"], 
    ['nick' => rand(), 'password' => rand(), "country" => "argentina"]
)->execute();
//$insert is true or false, depending if the query has been well proccesed
var_dump($insert);

//Update</h1>
//update nick when user_id = 1 and nick = 45
$update = $DB->update(['nick' => rand()])->where('user_id', 1)->where('nick', 45)->execute();
//update nick when user_id > 1
$update = $DB->update(['nick' => rand()])->where('user_id', '>', 1)->execute();

//$update is true or false, depending if the query has been well proccessed
var_dump($update);

//Select

//Select have a callback with the data
$DB->select('nick', 'user_id')->where('user_id', '>=', 5)->limit(10)->execute( function ( $error, Array $collection, $counter ) {
  if ( !$error ) {
    //The data as array
    var_dump($collection);
    //The data length
    var_dump($counter);
  }
  else {
    //error
  }
});

//$select is false if the query goes wrong or an array with the data
$select = $DB->select('nick', 'user_id')->where('user_id', '>=', 5)->limit(10)->execute();


//Delete is true or false, depending if the query is well proccessed
$delete = $DB->delete()->where('nick', 'tomasito')->where('user_id', '>', 15)->execute();
var_dump( $delete );
