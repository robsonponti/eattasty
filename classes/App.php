<?php

namespace classes;

use classes\DB;

class App extends DB{
	
	
	public $layout;

	public function __construct(){

		parent::__construct();

	}


	public function view($view){

    	if(is_file($this->layout)){

    		require_once $this->layout;

    	}

		$this->render($view);
	}




    public function render($render=null){


    	$file = 'views/'.$render.'.phtml';

        if (!is_null($render)){

            $file = is_null($file) ? $path : $file;

            file_exists($file) ? include_once($file) : include_once('views/404.phtml');

        }

    }





	
}

?>