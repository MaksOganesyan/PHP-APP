<?php

namespace app\Router;

class View
{
 public static function on_Main()
 {
  include dirname(__DIR__,1) . '/Views/main.php';
  exit();
 }
 public static function on_Login()
 {
  include dirname(__DIR__,1) . '/Views/login.php';
  exit();
 }
 public static function on_Register()
 {
  include dirname(__DIR__,1) . '/Views/register.php';
  exit();
 }
 public static function on_Article()
 {
  include dirname(__DIR__,1) . '/Views/article.php';
  exit();
 }
 
 
 
}
