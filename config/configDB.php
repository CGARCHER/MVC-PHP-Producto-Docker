<?php
use  Cgarcher\Fix\Database\PDO;

class configDB {

     private static PDO $instance;
     private static $host;
     private static $user;
     private static $pass;


     public function __construct(){
        //Compruebo si esta inicilizado
        if(!isset(self::$instance)){
            //recuperar los valores del .ini
            $this->getValues();

            //Crear la conexion
            $this->connect();
        }
     }

     private function connect(){
        self::$instance = new PDO(self::$host,self::$user,self::$pass);
     }

     private function getValues(){
        
        // 1) Si estamos en Render, pues hay que usar variables de entorno
        if (getenv('DB_HOST')) {

            $host = getenv('DB_HOST');
            $name = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASSWORD');
            $port = getenv('DB_PORT') ?: 3306;

            // construimos el DSN igual que esta en config.ini
            self::$host = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            self::$user = $user;
            self::$pass = $pass;

        }
        else{
            // 2) En local,  leer config.ini como siempre
            $conf = parse_ini_file('config.ini');

            self::$host = $conf['host']; 
            self::$user = $conf['user'];
            self::$pass = $conf['pass'];
        }

     }

     /**
      * Get the value of instance
      */ 
     public function getInstance()
     {
          return self::$instance;
     }
}

?>