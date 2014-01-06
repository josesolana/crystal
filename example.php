<?php
require_once 'classes/API.php';

API::setTable('users');
$Query = new API();
//$select debe ser retornar el objeto QueryConstructor
//Como no se introdujo ningún campo específico, la función getStatement debe de retornar '*'
/*
print'<h1>Insert</h1>';
$insert = $Query->insert(['nick' => 'tomi'.rand(), 'password' => "eoa", "country" => "argentina", "other" => 5], ['nick' => 'alguien'.rand(), 'password' => "amor", "country" => "argentina", "other" => 5])->execute();
var_dump($insert);
*/
print '<h1>Update</h1>';
$update = $Query->update(['nick' => 'tomasito'])->where('user_id', 1)->execute();
var_dump($update);

print '<h1>Select</h1>';
$select = $Query->select('nick', 'user_id')->limit(10)->execute( function ( $error, Array $collection, $counter ) {
  if ( !$error ) {
    var_dump($collection);
    var_dump($counter);
  }
  else {
    var_dump('Error');
  }
});


print '<h1>Delete</h1>';
$delete = $Query->delete()->where('nick', 'tomasito')->execute();
var_dump( $delete );