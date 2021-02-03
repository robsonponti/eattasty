$(document).ready(function(){

	$('.input-quantity-wrapper .range-buttons').click(function(evt){
		evt.preventDefault();
			var input = $(this).closest('.input-quantity-wrapper').find('.range-input')[0];
			var value = input.value;

		if($(this).hasClass('range-minus')){
			if(value > 1){
				input.value = value - 1;
			}
		}else{
				var new_value = 1;
				new_value += Number(value);
				input.value = new_value;
			

		}


	});




	ajax = {

		submit : function(forms, on_success, on_error){

			forms.each(function(index){

				formData = new FormData($(this)[0]);

				$.ajax({
					url:$(this).attr('action'),
					type: 'POST',
					success : function(data){
						try{
						
						data = JSON.parse(data);
						
						if(typeof(on_success) == 'function'){
							on_success(data);
						}		
						
						}catch(e){
							if(typeof(on_error) == 'function'){
								on_error(data);
							}
						}
					},
					error : function(jqXHR, textStatus, errorThrown){
							if(typeof(on_error) == 'function'){
								on_error(data);
							}
					},
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
				});
			});
		}
	};


	on_success = function(data){}

	on_error = function(data){}



	$(document.body).on('click', '#doLogin', function(evt){

		evt.preventDefault();


		var form = $(this).closest('form');

		on_success = function(data){

			location.href = 'home';

		}

		on_error = function(error){

			console.log('error');
		}

		ajax.submit(form, on_success, on_error);

	});



	cart = {

		add : function(el){

			Loading();

			var itemId = $(el).attr('item-id');
			var quantity = $(el).closest('.order-summary-footer').find('input[name=quantity]').val();


			$.ajax({
				url:'home/add_cart',
				type:'POST',
				dataType:"JSON",
				data:{
					id:itemId,
					quantity:quantity
				},
				success : function(data){

				$('body').toggleClass('showing-overlay-data');
				$('.order-details').removeClass('active');
				$(".header-cart-holder #cart-list-items").empty();
				$('.cart-count').empty().text(data.num_items);

					cart.cart();

				},
				error : function(error){

					console.log(error)

				}
			});



		},

		remove : function(itemId){

			$.ajax({
				url:'home/remove_item',
				type:'POST',
				dataType:"JSON",
				data:{
					id:itemId,
				},
				success : function(data){
					$(".header-cart-holder .cart-items").empty();
					cart.cart();

				},
				error : function(error){

					console.log(error)

				}
			});


		},

		show : function(){

				
				$('body').toggleClass('showing-overlay-data');
				$('.header-cart-holder').toggleClass('active');


		},
		close : function(){

				$('body').removeClass('showing-overlay-data');
				$('.header-cart-holder').removeClass('active');
			

		},
		detail : function(itemId){

			Loading();

			$.ajax({
				url:'home/product_details',
				type:'POST',
				dataType:"JSON",
				data:{
					id:itemId
				},
				success : function(data){

					var meal = data.item.meal;
					var image = meal.image != '' && meal.image != null ? meal.image : '';
					var ingredients = data.item.ingredients;
					var allergenics = data.item.allergenic;
					var extras = data.item.extras;
					var rating = parseInt(meal.rating);

					$('.order-items-details .order-summary #meal-title').empty().text(meal.title);
					$('.order-items-details .order-summary #meal-description').empty().text(meal.description);
					$('.order-items-details .order-image img').empty().attr('src',image);
					$('.order-items-details #allergenics').empty().text(allergenics);
					$('.order-items-details .order-summary input[name=quantity]').val(1);
					$('.order-items-details .order-summary-footer').find('.range-plus, .range-minus, .add-to-cart').attr('item-id', itemId);
					$('.order-items-details #ingredients-list').empty();
					$('.order-summary-value .order-item-value').empty().text(meal.price);


					$('#ingredients-list').empty();

					if(ingredients.length > 0){

						for(var i=0; i < ingredients.length; i++){

							$('#ingredients-list').append('<p class="list-addon-row ">'+
					               '<label>'+
					                   '<input type="checkbox" checked name="ingredient"> '+
					                    ingredients[i].ingredient+
					                  '</label>'+
					                '</p>');

						}

					}

					$('#extras-list').empty();

					if(extras.length > 0){
					

						$('.summary-extras').removeClass('hidden');

						for(var e=0; e < extras.length; e++){

							$('#extras-list').append('<p class="list-addon-row ">'+
					               '<label>'+
					                   '<input type="checkbox" name="extra"> '+
					                    extras[e].ingredient+
					                  '</label>'+
					                '</p>');

						}

					}else{

						$('.summary-extras').addClass('hidden');


					}

					if(rating != null){

						$('.order-items-details .rating-stars').empty();


						for(var s=1; s <= rating; s++ ){
							
							$('.order-items-details .rating-stars').append('<li><i class="fas fa-star rating-icon-star-filled"></i></li>');
						
						}

					}



					setTimeout(function(){ 
						$('body').addClass('no-scroll');
						$('.order-details').addClass('active'); 
					}, 200);


				},
				error : function(error){

				}

			});

		},
		details_update : function(itemId, quantity){

			Loading();

			$.ajax({
				url:'home/update_details',
				type:'POST',
				dataType:"JSON",
				data:{
					id:itemId,
					quantity:quantity
				},
				success : function(data){

					$('.order-summary-value .order-item-value').empty().text(data.item.total)

				},
				error : function(error){

					console.log(error)

				}
			});



		},
		cart : function(){

	  		$(".header-cart-holder #cart-list-items").empty().load("home/cart", function(responseTxt, statusTxt, xhr){



	  		});



		}

	}


	$('.order-summary-footer .range-minus, .order-summary-footer .range-plus').click(function(){

			var itemId = $(this).attr('item-id');
			var quantity = $(this).closest('.input-quantity-wrapper').find('input[name=quantity]').val();

			cart.details_update(itemId, quantity);

	});

	$('.customize-order-button').click(function(){
		if($(this).hasClass('show-ingredients')){
			$('.order-summary-ingredients-wrapper').slideDown();
			$('.customize-order').fadeOut();
		}else{
			$('.order-summary-ingredients-wrapper').slideUp();
			$('.customize-order').fadeIn();
		}
	});



	$(document.body).on('click', '.close-order-details', function(){

		$('.order-details').removeClass('active');
		$('body').removeClass('no-scroll');

	});


	$('.account-link, .close-account-button').click(function(){

		$('.header-account-holder').toggleClass('active');
	});


	function Loading(){

		$('.main-content').append('<div class="spinner-wrapper"><div class="spinner"></div></div>');

		setTimeout(function(){

			$('.spinner-wrapper').remove();

		}, 500);
	}

	$('.header-account-holder .form-tab .btn').click(function(){

		
		$(this).addClass('active').closest('.form-tab').find('.btn').not(this).removeClass('active');

		$('.form').find('.form-item[id="'+$(this).attr('tab')+'"]').toggleClass('active')
		.closest('.form').find('.form-item').not('.form-item[id="'+$(this).attr('tab')+'"]').toggleClass('active');

	});
});