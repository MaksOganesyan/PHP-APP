<?php
    namespace App\Models;
    
    class Test {
        public function __construct() {
            echo "Автозагрузка работает!";
        }

        public function next_pages() {
            header("Location: public/pages/public-test.php");
        }
    }
