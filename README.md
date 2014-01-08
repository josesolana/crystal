Crystal
=======

Simple database framework [``PHP 5.4+``]

Documentation
============
 - **Crystal::select**
 
`````php 
Crystal select( mixed $field_1 [, mixed $...] )
`````
 - **Crystal::insert**
 
`````php 
Crystal insert( array $assoc [, array $... ])
`````
 - **Crystal::update**
 
`````php 
Crystal update( array $assoc )
`````
 - **Crystal::delete**
 
`````php 
Crystal delete( void )
`````
   
*The following methods must be chained from a query method*

 - **Crystal::where** (chained from ``[select|update|delete]``)

`````php 
Crystal where( mixed $field, [ string comparison ], mixed $value )
`````
 - **Crystal::orWhere** (chained from ``[select|update|delete]``)

`````php 
Crystal orWhere( mixed $field, [ string comparison ], mixed $value )
`````

 - **Crystal::execute**

`````php
mixed execute ( [ callable $callback ( boolean $error, Array $result, Integer $counter ) ] )
`````

Usage
=====
 - **Settings**

First of all, you must enter your data in the configuration file ``/config/database.json``.

Here is a list of the settings:

 - ``driver``
   - *MySql*
   - *PostgreSql*
   - *MS SQL Server*
   - *Firebird*
   - *IBM*
   - *Informix*
   - *Cubrid*
   - *Oracle*
   - *ODBC*
   - *DB2*
   - *SQLite*
   - *4D*
 - ``dbname``
 - ``hostname``
 - ``user``
 - ``pass``

All the configuration settings must be wrapped in the "connection" key, as shown below:


`````json
{
  "connection" : [{
    "driver" : "mysql",
    "dbname" : "mydbname",
    "hostname" : "127.0.0.1",
    "user" : "root",
    "pass" : ""
  }]
}
`````

Once that you set the configuration file, then you are able to use the api.
 - **Initialization**

`````php
use Crystal\main as Crystal;
Crystal::setTable('table_name');
$DB = new Crystal();
`````
   
Example
=======
`````php
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
//$insert is true or false, depending if the query has been well processed
var_dump($insert);

//Update</h1>
//update nick when user_id = 1 and nick = 45
$update = $DB->update(['nick' => rand()])->where('user_id', 1)->where('nick', 45)->execute();
//update nick when user_id > 1
$update = $DB->update(['nick' => rand()])->where('user_id', '>', 1)->execute();

//$update is true or false, depending if the query has been well processed
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


//Delete is true or false, depending if the query is well processed
$delete = $DB->delete()->where('nick', 'tomasito')->where('user_id', '>', 15)->execute();
var_dump( $delete );

`````

Pretty Soon
====
Here is a list of the future implementations of the API:
 - ~~*Chainning in any combination*~~
 - *Transaction methods*
 - *Custom queries*
 - *Extended SQL syntax (``join, using, on, ...``)*
 - *Process stored procedures*
 - *Cache options*
 - *read and write connections*
 - ~~*Boolean values in ``where()``*~~
 - *Error handling*
 - ~~*Namespaces*~~ 
 - ~~*Better file organization*~~
 - *Autoload classes*
 - *More sql implementations (``create user, set grant, create database, truncate table, delete database, ...``)*

Contact
=======
If you have any doubts or want to help, please contact me tomirammstein@gmail.com
