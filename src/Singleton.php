<?php

namespace Crystal;

trait Singleton {
  
  protected static $_instance;
  
  public final static function getInstance() {
    
    return isset ( static::$_instance )
            ? static::$_instance
            : static::$_instance = new static
            ;
  }
    
  private final function __wakeup() { }
    
  private final function __clone() { }    
}
