<?php

namespace classes;

use classes\App;
use classes\DB;

class login extends App{

	private $id;
	private $password;
	private $username;
	private $role;

	public function __construct(){

		parent::__construct();
		
		$this->layout = 'layout.phtml';


	}

	public function index(){

		$this->view('login');
		
	}


	public function check(){

		$this->username = isset($_POST['username']) ? $_POST['username'] : '';
		$this->password = $_POST['password'];


		if($this->valid()){	

			$_SESSION['user']['id'] = $this->id;
			$_SESSION['user']['role'] = $this->role;


			echo json_encode(array('result'=>'success'));
			die;
		
		}else{

			echo json_encode(array('result'=>'error', 'message'=>'Credentials not valid.'));
			die;

		}

	}


	private function valid(){

		$user = $this->Query("SELECT * FROM users WHERE username = '{$this->username}' AND active = 1"); 

		if(!empty($user)){
			
			$this->id = $user[0]->id;
			$this->role = $user[0]->role;

			if(password_verify($this->password, $user[0]->password)){

				return true;

			}else{

				return false;
			}

		}

	}
	
}

?>