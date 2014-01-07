<?php

namespace Crystal;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Singleton.php'; 

use Crystal\Singleton;

class DataBase { 
  
  use Singleton;
  
  private static 
      $_configDir  = 'config/',
      $_file       = 'database.json'
      ;
  
  /**
   * The main key of the json file configuration
   */
  const CONFIG_KEY = 'connection';
  
  /**
   * Data required to connect 
   */
  private 
      $driver      = '',
      $hostname    = '',
      $dbname      = '',
      $user        = '',
      $pass        = '',
      $persistence = false,
      $_configData = Array (),
      $_connection = false   
      ;
  
  /**
   * Get only one instance
   */
  
  private function __construct () {
    try {
      $this->openConfig()->setData();
      return $this->_connection = new \PDO($this->driver.':host='.$this->hostname.';dbname='.$this->dbname, $this->user, $this->pass);
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
    $iterator = new \RecursiveArrayIterator (
      new \RecursiveArrayIterator ( json_decode ( $this->_configData, true ) )
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
