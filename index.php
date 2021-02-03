<?php

ob_start();
session_start();

	require_once "Bootstrap.php";
	require_once "classes/App.php";

			$app = new \classes\App();
		 	$app::render(); 


		    @$url = isset($_GET['url']) ? explode('/', $_GET['url']) : '';

		    $uri = !isset($url) | empty($url[0]) ? 'home' : $url[0];
		    $act = isset($url[1]) && !empty($url[1]) ? $url[1] : 'index';


		    if(class_exists('\\classes\\'.$uri)){
		        

		        $class = '\\classes\\'.$uri;
		        $run = new $class();
		            
		        if(method_exists($class, $act)){
		            
		            $run->$act();

		        }
		    }



?>
