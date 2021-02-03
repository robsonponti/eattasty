<?php

    //AUTO INICIAR CLASSES
    spl_autoload_register(function($class){


        $file = str_replace('\\', '/', $class) . '.php';

        if(file_exists($file)){

            require_once $file;

        }
            


        //LIMPAR CAMPOS PASSADOS VIA FORM
        if(isset($_POST)){


            foreach($_POST as $key => $vPost){

                $_POST[$key] = filter_var($vPost, FILTER_SANITIZE_MAGIC_QUOTES);
            
            } 

        }


       });







?>

