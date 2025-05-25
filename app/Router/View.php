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
  include dirname(__DIR__,1) . '/Views/list_articles.php';
  exit();
 }
 public static function on_CreateArticle()
 {
  include dirname(__DIR__,1) . '/Views/create_article.php';
  exit();
 }
 public static function on_EditArticle($id)
 {
  include dirname(__DIR__,1) . '/Views/edit_article.php';
  exit();
 }
 
 
 
}
