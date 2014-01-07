<?php

namespace Crystal;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'QueryConstructor.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DataBase.php';

use Crystal\QueryConstructor
  , Crystal\DataBase 
  ;

/**
 * Abstraction Layer
 */
class main extends QueryConstructor
{
  
  public function __construct () {
    parent::__construct ( DataBase::getInstance() );
  }
}
