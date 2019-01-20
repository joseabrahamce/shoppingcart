<?php 
/**
 *  Cart Class Controller
 *	Performs all actions refered to the cart: show list, add to cart,
 *	delete from cart and process payment
 */
class Cart extends Controller
{
	
	public function __construct()
	{
		$this->productModel = $this->createModel('Product');
 		$this->cartModel = $this->createModel('Cart');
	}

	public function __call($name, $arguments) {
		header('Location: ' . URLROOT);
	}

	/**
	* Add item to cart
	*	Sanitize $_POST data before sending it to the model
	* @return void
	*/
	public function add() : void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['quantity'])) 
		{
			$data = [
				"id" => $_POST['product_id'],
				"quantity" => $_POST['quantity'],
				"subtotal" => ($_POST['quantity'] * $_POST['price'])
			];

			// If value > 0
			if ($_POST['quantity'] > 0) {
				if ($this->cartModel->addItem($data))
					header('Location: ' . URLROOT);
				else 
					die("Something went wrong");
			}

			// Check that post quantity is integer
			$pattern = '/^\d[^\.]*$/';
			if (!preg_match_all($pattern, $data['quantity'])) {
				$data = $this->setErrorMessage("Invalid value");
				$this->loadView('index', $data, $this->cartModel->getCart());
			}
		} else {
			// If quantity = '', redirects to homepage
			header('Location: ' . URLROOT);
		}
	}

	/**
	* Delete item from cart
	* @return void
	*/ 
	public function delete() : void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($this->cartModel->deleteItem(intval($_POST['product_id']))) 
				header('Location: ' . URLROOT);
			else
				echo "Something went wrong";
		} else {
			echo "GET";
		}
	}

	/**
	* Set Error Message
	* Get all items, find the one where the error message should be, and set the message
	* @param string 	Message to show
	* @return array 	Data with error message
	*/ 
	private function setErrorMessage(string $message) : array
	{
		$data = $this->productModel->getItems();
		foreach ($data as &$item) {
			if ($item['product_id'] == $_POST['product_id']) 
				$item['quantity_err'] = "*$message";
		}
		return $data;
	}
}