<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'QueryConstructor.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DataBase.php';

/**
 * Abstraction Layer
 */
class Crystal extends QueryConstructor
{
  
  public function __construct () {
    parent::__construct ( DataBase::getInstance() );
  }
}
