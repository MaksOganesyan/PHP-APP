<?php

namespace app\Router;

class View
{
 public static function on_Main()
 {
  include dirname(__DIR__,1) . '/Views/register.php';
  exit();
 }
}
