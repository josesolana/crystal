Crystal
=======

Simple database framework

Usage
=====
First of all, you must enter your data in the configuration file (/config/database.json).

Here is a list of the settings:

 - driver
   - MySql
   - PostgreSql
   - MS SQL Server
   - Firebird
   - IBM
   - Informix
   - Cubrid
   - Oracle
   - ODBC
   - DB2
   - SQLite
   - 4D
 - dbname
 - hostname
 - user
 - pass

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

Once that you set the configuration file, then you are ablre to use the api.
 - Initialization
   `````php
   API::setTable('table_name');
   $DB = new API();
   `````
 - Select
   ======
   ``Crystal select( mixed $field_1 [, mixed $...] )``
 - Insert
   ======
   ``Crystal insert( array $assoc [, array $... ])``
 - Update
   ======
   ``Crystal update( array $assoc )``
 - Delete
   ======
   ``Crystal delete()``
The following methods must be chained from a query method
 - Where (chained from [select|update|delete])
   =====
   ``Crystal where( mixed $field, [ string comparison ], mixed $value )``
 - Limit (chained from [select])
   =====
   ``Crystal limit( Integer $limit )``
