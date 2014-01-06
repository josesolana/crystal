<?php

trait Singleton
{
  protected static $_instance;
    final public static function getInstance()
    {
        return isset(static::$_instance)
            ? static::$_instance
            : static::$_instance = new static;
    }
    
    final private function __wakeup() {
      
    }
    
    final private function __clone() {
      
    }    
}

/**
 * Class DataBase uses PDO
 */

class DataBase { 
  
  /**
   *
   * @var type String
   * this is the folder of the database.json file
   */
  private static $_configDir = 'config/';
  
  /**
   *
   * @var type String
   * The file name of the database configuration
   */
  private static $_file = 'database.json';
  /**
   *
   * @var type Array
   * The data readed from the json file configuration
   */
  private $_configData;
  
  /**
   * The main key of the json file configuration
   */
  const CONFIG_KEY = 'connection';
  
  /**
   * Data required to connect 
   */
  private $driver;
  private $hostname;
  private $dbname;
  private $user;
  private $pass;
  
  private $persistence;
  
  /**
   * Get only one instance
   */
  use Singleton;
  
  private $_connection = false;
  
  private function __construct () {
    try {
      $this->openConfig()->setData();
      $this->_connection = new PDO($this->driver.':host='.$this->hostname.';dbname='.$this->dbname, $this->user, $this->pass);
      return $this->_connection;
    } 
    catch ( PDOException $e ) {
      throw new Exception('Could not connect to de database.');
    }
  }
  
  /**
   * Open the configuration file
   * @return \DataBase
   * @throws Exception
   */
  private function openConfig () {
    $dir = getcwd().DIRECTORY_SEPARATOR.self::$_configDir;
    if ( is_dir ( $dir ) ) {
      $file = $dir.DIRECTORY_SEPARATOR.self::$_file;
      if ( file_exists( $file ) ) {
        $this->_configData = file_get_contents($file);
        return $this;
      }
      throw new Exception('This file does not exists');
    }
    throw new Exception('This directory does not exists');
  }
  
  /**
   * Set all the data readed
   * @return boolean
   * @throws Exception
   */
  private function setData () {
    $iterator = new RecursiveArrayIterator (
      new RecursiveArrayIterator ( json_decode ( $this->_configData, true ) )
    );
    
    if ( in_array ( self::CONFIG_KEY, array_keys ( (Array) $iterator ) ) ) {
      array_walk( $iterator [ self::CONFIG_KEY ], function ( $values, $keys ) {
        foreach ( $values as $key => $value ) {
          $this->$key = $value;
        }
      });
      return true;
    }
    throw new Exception('The connection key does not exists');
  }
  
  /**
   * Operations
   */
  
  public function executeSelect ( $query, Array $values ) {
    $statement = $this->_connection->prepare( $query );
    try {
      if ( $statement->execute( $values ) ) {
        return $statement->fetchAll();
      }
    }
    catch ( PDOException $e ) {
      throw new Exception( $e->getMessage() );
    }
    return false;
  }
  
  public function executeCount ( $query, Array $values ) {
    $statement = $this->_connection->prepare($query);
    try {
      if ( $statement->execute( $values ) ) {
        return $statement->rowCount();
      }
    } 
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
    return false;
  }
  
  public function executeUpdate ( $query, Array $values ) {
    $statement = $this->_connection->prepare($query);
    try {
      return $statement->execute( $values );
    } 
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  
  public function executeDelete ( $query, Array $values ) {
    $statement = $this->_connection->prepare($query);
    try {
      return $statement->execute( $values );
    } 
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  
  public function executeInsert ( $query, Array $values ) {
    $statement = $this->_connection->prepare($query);
    try {
      return $statement->execute( $values );
    } 
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }
  
  public function executeTruncate ( $query ) {
    /**
     * Soon
     */
  }
}

/**
 * This class set all the querys syntax
 * Class QueryConstructor
 */
class QueryConstructor 
{
  /**
   * Index of where() params
   * WHERE_VALUE_INDEX_MIN and WHERE_VALUE_INDEX are used when where() have 2 arguments or 3, respectivelly
   */
  const WHERE_KEY_INDEX       = 0;
  const WHERE_COMPARE_INDEX   = 1;
  const WHERE_VALUE_INDEX_MIN = 1;
  const WHERE_VALUE_INDEX     = 2;
  
  private static $DB;
  
  private static $_table;
  private $_operation;
  private $_statement;
  private $_hasWhere = false;
  private $_values = [ ];
  
  public function __construct ( DataBase $db ) {
    self::$DB = $db;
  }
  
  public static function setTable ( $table ) {
    if ( ! empty ( $table ) ) {
      self::$_table = $table;
      return true;
    }
    throw new Exception('The table argument can not be empty.');
  }
  
  /**
   * Shit matrix
   * @param array $matrix
   * @param mixed $element
   * @param Integer $index
   * @return Array $matrix
   */
  private function array_insert ( Array $matrix, $element, $index = 0 ) {
    if ( $index == 0 ) {
      array_unshift($matrix, $element);
    }
    else {
      $length = count ( $matrix ) - 1;
      if ( $length >= $index ) {
        for ( $i = $length; $i > 0; $i-- ) {
          $matrix [ $i + 1 ] = $matrix [ $i ];
          if ( $i == $index ) {
            $matrix [ $i ] = $element;
            break;
          }
        }
      }
      else {
        $matrix [ $length + 1 ] = $element;
      }
    }
    return $matrix;
  }
  
  /**
   * Concat all the arguments and returns it as a string
   * @return String
   */
  private function concat (/*infinite arguments*/) {
    $arguments = func_get_args();
    $output = '';
    foreach ( $arguments as $argument ) {
      $output.= $argument. ' ';
    }
    return (string) trim ( $output );
  }
  
  /**
   * Returns the associative part [boolean or not] of a query statement
   * @param array $parts
   * @param boolean $assoc
   * @param boolean $colon
   * @param boolean $booleanAssoc
   * @return String
   */
  private function buildParts ( Array $parts, $assoc = false, $colon = false, $booleanAssoc = true ) {
    $output = '';
    $counter = 0;
    $items = count ( $parts );
    foreach ( $parts as $part ) {
      //if is assoc
      if ( $assoc ) {
        if ( ++$counter < $items ) {
          $output.= ( $booleanAssoc ) ? $part.' = :'.$part.' AND ' : $part.' = :'.$part.', ';
        }
        else {
          $output.= $part.' = :'.$part;
        }
      }
      //comma
      else {
        //colons?
        if ( $colon ) {
          $part = ':'.$part;
        }
        if ( ++$counter < $items ) {
          $output.= $part.', ';
        }
        else {
          $output.= $part;
        }
      }
    }
    //statement portion
    return (string) $output;
  }
  
  private function buildAssocValues ( $key, $value = null ) {
    $assoc = [ ];
    $key = ':'.$key;
    $assoc = array_merge( $assoc, [ $key => $value ]);
    return $assoc;
  }
  
  /**
   * Where portion
   * @return \QueryConstructor
   * @throws Exception
   */
  public function where ( /*multiple arguments*/ ) {
    $this->operation = 'select';
    //calback
    $arguments = func_get_args();
    $argumentsLength = func_num_args();
    /**
     * If there are two parameters, an equal relationship is established
     * If there are three parameters, the comparison criterion is explicitly setted
     */
    $compare = '=';
    switch ( $argumentsLength ) {
      case 2 :
        $key   = $arguments [ self::WHERE_KEY_INDEX ];
        $value = $arguments [ self::WHERE_VALUE_INDEX_MIN ];
      break;
      case 3 :
        $key     = $arguments [ self::WHERE_KEY_INDEX ];
        $compare = $arguments [ self::WHERE_COMPARE_INDEX ];
        $value   = $arguments [ self::WHERE_VALUE_INDEX ];
      break;
      default :
        throw new Exception('La función '.__FUNCTION__.' debe de tener entre 2 y 3 parámetros.');
      break;
    }
    
    $this->_values = array_merge ( $this->_values, $this->buildAssocValues($key, $value) );
    $this->_statement = ( ! $this->_hasWhere ) 
            ? $this->concat($this->_statement, 'WHERE', $key, $compare, ':'.$key) 
            : $this->concat($this->_statement, 'AND', $key, $compare, ':'.$key);
   
    /**
     * Where is already used
     * This because the second where, will be replaced with de AND keyword
     * Pretty soon the user will be able to use another boolean value
     */
    $this->_hasWhere = true;
    
    return $this;
 
  }
  
  /**
   * Limit statement portion
   * @param type $number
   * @return \QueryConstructor
   * @throws Exception
   */
  public function limit ( $number ) {
    if (is_integer( $number ) ) {
      $this->_statement = $this->concat( $this->_statement, 'LIMIT', $number );
      return $this;
    }
    throw new Exception(__FUNCTION__.' parameter must be a number.');
  }
  
  /**
   * Execute the statement
   * @return mixed
   */
  public function execute () {
  
    $execute;
    $arguments = func_num_args();
    
    switch ( $this->_operation ) {
      case 'select' :
        /**
         * Temporal solution
         */
        $this->_statement = ( $this->_hasWhere ) ? $this->_statement : preg_replace('/\(|\)/', '', $this->_statement);
        
        $execute = self::$DB->executeSelect($this->_statement, $this->_values);
        if ( $arguments > 0 ) {
          $callback = func_get_arg(0);
          if ( is_callable( $callback ) ) {
            /**
             * If $execute is an Array, the query has been procces, otherwise, returns false.
             * If $execute is false, the second parameter will be a null Array             
             * 
             */
            call_user_func($callback, !is_array($execute), $execute ?: [ ], count($execute));
          }
        }
        break;
          
      case 'insert' :
        /*Set all the values for the statement*/
        foreach ( $this->_values as $value ) {
          $execute = self::$DB->executeInsert($this->_statement, $value);
          if ( !$execute )
            break;
        }
        break;
        
      case 'update' :
        $execute = self::$DB->executeUpdate($this->_statement, $this->_values);
        break;
      
      case 'delete' :
        $execute = self::$DB->executeDelete($this->_statement, $this->_values);
        break;      
    }
 
    return $execute;
  }
  
  /**
   * All the database basic operations
   * @returns \QueryConstructor
   */
  
  public function select ( /*multiple arguments*/ ) {
    
    $this->cleanData();
    $this->_operation = 'select';
    
    if ( ! self::$_table ) {
      throw new Exception('You must to specify a table using QueryConstructor::setTable(\'table_name\')');
    }
    
    $argumentsLength = func_num_args();
    
    if ( ! (boolean) $argumentsLength ) {
      $fields = '*';
    }
    else {
      $arguments = func_get_args();
      $fields = $this->concat('(', $this->buildParts( $arguments ), ')');
    }
    $this->_statement = $this->concat('SELECT',  $fields, 'FROM', self::$_table);
    return $this;
  }
  
  public function insert () {
    $this->cleanData();
    $this->_operation = 'insert';
    
    $temporalValues = [ ];
    
    $arguments = func_get_args();
    foreach ( $arguments as $elements ) {
      if ( is_array ( $elements ) ) { 

        array_walk( $elements, function ( $value, $key ) use ( &$temporalValues ) {
          $temporalValues = array_merge ( $temporalValues, $this->buildAssocValues($key, $value) );
        });
        
        $fields = $this->buildParts( array_keys ( $elements ) );
        $values = $this->buildParts( array_keys ( $elements ), false, true );

        $this->_statement = (Array) $this->_statement;
        $this->_statement = $this->concat('INSERT INTO', self::$_table, '(', $fields, ')', 'VALUES (', $values, ')');
        array_push( $this->_values, $temporalValues );
        
      }
      else {
        throw new Exception('Argumenst must be defined as Array');
      }
    }
    return $this;
  }
  
  public function update ( Array $sets ) {
    $this->cleanData();
    $this->_operation = 'update';
    
    $set = $this->buildParts( array_keys( $sets ),true, true, false);
    
    array_walk($sets, function ( $value, $key ) {
      $this->_values = array_merge($this->_values, $this->buildAssocValues($key, $value));
    });
  
    $this->_statement = $this->concat('UPDATE', self::$_table, 'SET', $set);
    return $this;
  }
  
  public function delete (/*multiple params*/) {
    $this->cleanData();
    $this->_operation = 'delete';
    
    $params = func_get_args();
    $fields = '';
    if ( ! empty ( $params ) ) {
      $fields = $this->buildParts( $params );
    }
    $this->_statement = $this->concat('DELETE', $fields, 'FROM', self::$_table);
    return $this;
  }
  
  private function cleanData () {
    $this->_hasWhere = false;
    $this->_values = [ ];
    $this->_statement = null;
  }
  
  public function getStatement () {
    return $this->_statement;
  }
  
  public function getValues () {
    return $this->_values;
  }
  
}


/**
 * Abstraction Layer
 */
class Crystal extends QueryConstructor
{
  
  public function __construct () {
    parent::__construct ( DataBase::getInstance() );
  }
}
