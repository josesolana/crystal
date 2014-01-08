<?php

namespace Crystal;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'QueryConstructor.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DataBase.php';

use Crystal\QueryConstructor as QueryConstructor
  , Crystal\DataBase as DataBase
  ;

/**
 * Abstraction Layer
 */
class main {
  
  private $Constructor = null,
          $DataBase    = null
  ;
  
  public function __construct () {
    $this->Constructor = new QueryConstructor(); 
    $this->DataBase    = DataBase::getInstance();
  }
  
  /**
   * Delegator pattern
   */
  
  public static function setTable ( $table ) {
    QueryConstructor::setTable($table);
  }
  
  public function select () {
    call_user_func_array( Array ( $this->Constructor, 'select' ), func_get_args() );
    return $this;
  }
  
  public function insert () {
    call_user_func_array( Array ( $this->Constructor, 'insert' ), func_get_args() );
    return $this;
  }
  
  public function update ( Array $sets ) {
    call_user_func( Array ( $this->Constructor, 'update' ), $sets );
    return $this;
  }
  
  public function delete () {
    call_user_func_array( Array ( $this->Constructor, 'delete' ), func_get_args() );
    return $this;
  }
  
  public function where () {
    call_user_func_array( Array ( $this->Constructor, 'where' ), func_get_args() );
    return $this;
  }
  
  public function orWhere () {
    call_user_func_array( Array ( $this->Constructor, 'orWhere' ), func_get_args() );
    return $this;
  }


  public function limit ( $integer ) {
    call_user_func_array( Array ( $this->Constructor, 'limit' ), func_get_args() );
    return $this;
  }
  
  /**
   * Getters
   */
  
  private function getFinalStatement () {
    return $this->Constructor->getStatement();
  }
  
  private function getValues () {
    return $this->Constructor->getValues();
  }
  
  /**
   * Execute the statement
   * @return mixed
   */
  public function execute () {
    $statement = $this->getFinalStatement();
    $values    = $this->getValues();
    
    switch ( $this->Constructor->getCurrentOperation() ) {
      case 'select' :

        $execute = $this->DataBase->executeSelect ( $statement, $values );
        if ( func_num_args() > 0 ) {
          if ( is_callable ( $callback = func_get_arg ( 0 ) ) ) {
            /**
             * If $execute is an Array, the query has been procces, otherwise, returns false.
             * If $execute is false, the second parameter will be a null Array             
             * 
             */
            call_user_func ( $callback, ! is_array ( $execute ), $execute ?: [ ], count ( $execute ) );
          }
        }
        break;
          
      case 'insert' :
        /*Set all the values for the statement*/
        foreach ( $values as $value ) {
          if ( ! $execute = $this->DataBase->executeInsert ( $statement, $value ) )
            break;
        }
        break;
        
      case 'update' :
        $execute = $this->DataBase->executeUpdate ( $statement, $values );
        break;
      
      case 'delete' :
        $execute = $this->DataBase->executeDelete ( $statement, $values );
        break;      
    }
    
    $this->Constructor->cleanData();
    return $execute;
  }
  
  /**
   * Pendientes
   */
  public function transaction () {
    
  } 
  
  public function processProcedure () {
    
  }
  
  public function truncate () {
    call_user_func_array( Array ( $this->Constructor, 'truncate' ), func_get_args() );
    return $this;
  }
  
  public function group () {
    
  }
  
  public function sort () {
    
  }
  
  public function join () {
    
  }
  
  public function leftJoin () {
    
  }
  
  public function sum () {
    
  }
  
  public function max () {
    
  }
  
  public function min () {
    
  }
  
  public function raw () {
    
  }
}
