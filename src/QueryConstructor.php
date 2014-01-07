<?php

namespace Crystal;

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
  const 
      WHERE_KEY_INDEX       = 0,
      WHERE_COMPARE_INDEX   = 1,
      WHERE_VALUE_INDEX_MIN = 1,
      WHERE_VALUE_INDEX     = 2
  ;
  
  private static 
      $_DB    = null,
      $_table = ''
  ;
  
  private $_operation = '',
          $_statement = '',
          $_hasWhere  = false,
          $_values    = Array ()
  ;
  
  
  public function __construct ( DataBase $db ) {
    self::$_DB = $db;
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
    $output = '';
    foreach ( func_get_args() as $argument ) {
      $output.= $argument. ' ';
    }
    return trim ( $output );
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
        if ( ++$counter < $items )
          $output.= ( $booleanAssoc ) ? $part.' = :'.$part.' AND ' : $part.' = :'.$part.', ';
        else
          $output.= $part.' = :'.$part;
      }
      //comma
      else {
        //colons?
        if ( $colon )
          $part = ':'.$part;
     
        $output .= ( ++$counter < $items ) ? $part . ', ' : $part;
      }
    }
    //statement portion
    return $output;
  }
  
  private function buildAssocValues ( $key, $value = null ) {
    $assoc = Array();
    $key = ':'.$key;
    return $assoc = array_merge( $assoc, [ $key => $value ]);
  }
  
  private function setWhereComparison ( $arguments, $argumentsLength ) {
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
        throw new Exception('Function where() must be 2 or 3 parameters');
        break;
    }
    return Array( $key, $compare, $value );
  }
  
  /**
   * Where portion
   * @return \QueryConstructor
   * @throws Exception
   */
  public function where ( /*multiple arguments*/ ) {
    $this->operation = 'select';
    $arguments       = func_get_args();
    /**
     * If there are two parameters, an equal relationship is established
     * If there are three parameters, the comparison criterion is explicitly setted
     */
    $arguments = $this->setWhereComparison( func_get_args(), func_num_args() );
    
    $this->_values = array_merge ( $this->_values, $this->buildAssocValues ( $arguments [ 0 ], $arguments [ 2 ] ) );
    
    $this->_statement = ( ! $this->_hasWhere ) 
            ? $this->concat($this->_statement, 'WHERE', $arguments [ 0 ], $arguments [ 1 ], ':'.$arguments [ 0 ]) 
            : $this->concat($this->_statement, 'AND', $arguments [ 0 ], $arguments [ 1 ], ':'.$arguments [ 1 ]);
   
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
    if ( is_integer( $number ) ) {
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
    
    switch ( $this->_operation ) {
      case 'select' :
        /**
         * Temporal solution
         */
        $this->_statement = ( $this->_hasWhere ) ? $this->_statement : preg_replace ( '/\(|\)/', '', $this->_statement );
        
        $execute = self::$_DB->executeSelect ( $this->_statement, $this->_values );
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
        foreach ( $this->_values as $value ) {
          if ( ! $execute = self::$_DB->executeInsert ( $this->_statement, $value ) )
            break;
        }
        break;
        
      case 'update' :
        $execute = self::$_DB->executeUpdate ( $this->_statement, $this->_values );
        break;
      
      case 'delete' :
        $execute = self::$_DB->executeDelete ( $this->_statement, $this->_values );
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
    
    if ( ! self::$_table )
      throw new Exception('You must to specify a table using QueryConstructor::setTable(\'table_name\')');
    
    if ( ! (boolean) func_num_args() ) 
      $fields = '*';
    else 
      $fields = $this->concat('(', $this->buildParts ( func_get_args() ), ')');
 
    $this->_statement = $this->concat ( 'SELECT',  $fields, 'FROM', self::$_table );
    return $this;
  }
  
  public function insert () {
    $this->cleanData();
    $this->_operation = 'insert';
    
    foreach (func_get_args() as $elements ) {
      if ( is_array ( $elements ) ) { 
        
        $temporalValues = Array ();
        
        array_walk( $elements, function ( $value, $key ) use ( &$temporalValues ) {
          $temporalValues = array_merge ( $temporalValues, $this->buildAssocValues($key, $value) );
        });
        
        $fields = $this->buildParts( array_keys ( $elements ) );
        $values = $this->buildParts( array_keys ( $elements ), false, true );

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
    if ( ! empty ( $params ) ) 
      $fields = $this->buildParts( $params );
 
    $this->_statement = $this->concat('DELETE', $fields, 'FROM', self::$_table);
    return $this;
  }
  
  private function cleanData () {
    $this->_hasWhere  = false;
    $this->_values    = Array();
    $this->_statement = null;
  }
  
}

