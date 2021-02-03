<?php

namespace classes;


class home extends App{

	public function __construct(){

		parent::__construct();

		$this->layout = 'layout.phtml';

	}


	public function index(){


		$this->view('home');
		

	}

	
	public function meals(){


		return $this->Query("SELECT * FROM meals WHERE active  = 1");


	}

	public function product_details(){


		if(isset($_POST['id']) && !empty($_POST['id'])){

			$product['meal'] = $this->get_item($_POST['id'])[0];
			$product['ingredients'] = $this->get_ingredients($_POST['id']);
			$product['extras'] = $this->get_extras($_POST['id']);
   			$product['allergenic'] = $this->get_allergenic($_POST['id']);

			echo json_encode(array("result"=>"success", "item"=>$product));

		}else{

			return null;
		}

	}

	private function get_extras($id){

		$extras = $this->Query("SELECT ingredient FROM ingredients AS i
							LEFT JOIN extra_ingredients AS e
							ON e.ingredient_id = i.id
							WHERE meal_id = {$id}
							");


		return !empty($extras) ? $extras :  '';

	}

	private function get_allergenic($id){


		$meal_allergenics = $this->Query("SELECT ingredient FROM ingredients AS i
							LEFT JOIN allergenic AS a
							ON a.ingredient_id = i.id
							WHERE i.id 
								IN (
									SELECT ingredient_id FROM allergenic WHERE ingredient_id = i.id
									AND IF(ingredient_id IN 
										(SELECT ingredient_id FROM meals_ingredients WHERE meal_id = {$id}), TRUE, FALSE
									)
								)
							");

		foreach ($meal_allergenics as $kA => $allergenic) {
			
			$allergenics[] = $allergenic->ingredient;

		}

		return isset($allergenics) ? (count($allergenics) > 1 ? implode(',', $allergenics) : $allergenics[0]) : '';

	}

	private function get_ingredients($id){

		return $this->Query("SELECT *FROM ingredients WHERE id IN (SELECT ingredient_id FROM meals_ingredients WHERE meal_id = {$id})");

	}

	private function get_item($id){

		return $this->Query("SELECT * FROM meals WHERE id = {$id}");

	}


	


	public function update_details(){

		$meal = $this->get_item($_POST['id']);
		$price = $meal[0]->price;

		$total = $price * $_POST['quantity'];
		$otal = abs($total);

		$product['meal'] = $meal[0];
		$product['ingredients'] = $this->get_ingredients($_POST['id']);
		$product['extras'] = $this->get_extras($_POST['id']);
   		$product['allergenic'] = $this->get_allergenic($_POST['id']);
   		$product['total'] = number_format($total, 2, '.', ',');

		echo json_encode(array("result"=>"success", "item"=>$product));

	}



	public function add_cart(){

		$meal = $this->get_item($_POST['id']);
		$price = $meal[0]->price;
		$total = $price * $_POST['quantity'];
		$total = abs($total);


		$qtItems = 0;



		if(isset($_SESSION['order']['items'])){

			$k = 0;

			foreach ($_SESSION['order']['items'] as $kI => $item) {
				

				if($item['id'] == $_POST['id']){


					$_SESSION['order']['items'][$kI]['quantity'] = $_SESSION['order']['items'][$kI]['quantity'] + $_POST['quantity'];
					$_SESSION['order']['items'][$kI]['total'] = number_format($total, 2, '.', ',');
					

				}
				
				$items[] = $item["id"];

				$qtItems = $qtItems + $_SESSION['order']['items'][$kI]['quantity'];


				$k++;
			}

				if(@!in_array($_POST['id'], $items)){

					$_SESSION['order']['items'][$k]['id'] = $_POST['id'];
					$_SESSION['order']['items'][$k]['quantity'] = $_POST['quantity'];
					$_SESSION['order']['items'][$k]['total'] = number_format($total, 2, '.', ',');


					$qtItems = $qtItems + $_SESSION['order']['items'][$k]['quantity'];

				}


		}else{

			$_SESSION['order']['items'][0]['id'] = $_POST['id'];
			$_SESSION['order']['items'][0]['quantity'] = $_POST['quantity'];
			$_SESSION['order']['items'][0]['total'] = number_format($total, 2, '.', ',');
			
			$qtItems = $qtItems + $_SESSION['order']['items'][0]['quantity'];

		}


		$order = $_SESSION['order'];

		echo json_encode(array("result"=>"success","order"=>$order,"num_items"=>$qtItems));
	}


	public function remove_item(){

			foreach ($_SESSION['order']['items'] as $kI => $item) {
				

				if($item['id'] == $_POST['id']){
					
					$_SESSION['order']['total'] = $_SESSION['order']['total'] - $item['total'];

					unset($_SESSION['order']['items'][$kI]);

				}

			}

		$old_items = $_SESSION['order']['items'];

		$new_items = array_splice($old_items, 0, count($old_items));


		unset($_SESSION['order']['items']);

		$_SESSION['order']['items'] = $new_items;


		echo json_encode(array("result"=>"success"));

	}


	public function cart(){

	if(count($_SESSION['order']['items']) > 0){

		foreach ($_SESSION['order']['items'] as $kI => $item) {

			$id = $item['id'];

			$meal = $this->Query("SELECT * FROM meals WHERE id = {$id}");

			$total = $meal[0]->price * $item['quantity'];

			$items[$kI]['quantity'] = $item['quantity'];
			$items[$kI]['title'] = $meal[0]->title;
			$items[$kI]['price'] = $meal[0]->price;
			$items[$kI]['image'] = $meal[0]->image;
			$items[$kI]['description'] = $meal[0]->description;
			$items[$kI]['total'] = !empty($total) ? number_format($total, 2, '.', ',') : '00.00';
			


			echo '<div class="cart-content-wrapper">
				<div class="cart-items">
				 <div class="cart-item">
						<div class="cart-item-thumb">
							<img src="'.$items[$kI]['image'].'">
						</div>
						<div class="cart-item-details">
							<h4 class="cart-item-title">'.$items[$kI]['title'].'</h4>
							<p class="cart-item-ingredients">'.$items[$kI]['description'].'</p>		
							<div class="cart-item-quantity-value">
								<div class="input-quantity-wrapper rounded">
									<input type="number" step="1" min="1" max="" name="quantity" class="range-input" value="'.$items[$kI]['quantity'].'" title="Quantidade" size="4" inputmode="numeric">
									<input type="button" value="-" class="range-buttons range-minus">
									<input type="button" value="+" class="range-buttons range-plus">
								</div>
								<div class="cart-item-value">
									<span class="cart-item-un-value">'.$items[$kI]['price'] .'€ un.</span>
									<span class="cart-item-total-value">'.$items[$kI]['total'].'€</span>
								</div>

							</div>
						</div>
						<a class="remove-button" onclick="cart.remove('.$id.');"><i class="far fa-trash-alt"></i></a>
					</div>
				</div>
			</div>';


			}

			echo '<div class="cart-total">
						<div class="cart-total-text">
							<span class="cart-total-title">Total</span>
							<h3 class="cart-total-value "><span id="cart-total">'.$this->cart_info()["total"].'</span>€</h3>
						</div>
						<div class="cart-total-buttons">
							<button class="btn btn-dark-red">Finalizar Compra</button>
						</div>
					</div>';


		}else{


			echo '<div class="cart-total text-center mt-5 mb-5">
					<div class="cart-total-text">
					<h2>Não há itens no cesto.</h2>
				</div>
			</div>';
		}


	}

	public function cart_info(){

		$quantity = 0;
		$total = 0;

		foreach ($_SESSION['order']['items'] as $kI => $item) {


			if(isset($_SESSION['order']['items'])){

				$quantity = $quantity + $item['quantity'];
				$total = $total + $item["total"];


			}

		}

		$info["total"] = isset($_SESSION['order']['items']) ? number_format($total, 2, ',','.') : '00.00';
		$info["items"] = $quantity;

		return $info;

	}

	public function list(){

		print_r($_SESSION['order']);
	}

	public function clean(){

		unset($_SESSION['order']);
	}
}

?>