<?php

/**
 * Shipy Pos System
 * @author  Mirarus <aliguclutr@gmail.com>
 */
class Shipy
{

	private $config = [];
	private $customer = [];
	private $product = [];
	private $currency_codes = ['TRY', 'EUR', 'USD', 'GBP'];
	private $page_lang_codes = ['TR', 'EN', 'DE', 'AR', 'ES', 'FR'];
	private $mail_lang_codes = ['TR', 'EN'];
	private $currency_code;
	private $page_lang_code;
	private $mail_lang_code;

	public function setConfig($data=[])
	{
		if ($data['type'] == 'init') {
			if ($data['api_key'] == null || $data['payment_method'] == null) {
				exit("Missing api information.");
			} else{
				$this->config = [
					'api_key' => $data['api_key'],
					'payment_method' => $data['payment_method']
				];
			}
		} if ($data['type'] == 'callback') {
			if ($data['api_key'] == null) {
				exit("Missing api information.");
			} else{
				$this->config = [
					'api_key' => $data['api_key']
				];
			}
		} 
	}

	public function setCustomer($data=[])
	{
		if ($data['name'] == null || $data['email'] == null || $data['phone'] == null || $data['address'] == null) {
			exit("Missing customer information.");
		} else{
			$this->customer = [
				'name' => $data['name'],
				'email' => $data['email'],
				'phone' => $data['phone'],
				'address' => $data['address']
			];
		}
	}
	
	public function setProduct($data=[])
	{
		if ($data['order_id'] == null || $data['amount'] == null) {
			exit("Missing product information.");
		} else{
			$this->product = [
				'order_id' => $data['order_id'],
				'amount' => $data['amount'],
				'installment' => (isset($data['installment']) ? $data['installment'] : null)
			];
		}
	}

	public function setLocale($data=[])
	{
		if (in_array($data['currency'], $this->currency_codes)) {
			$this->currency_code = $data['currency'];
		} else{
			exit("Invalid Currency Code");
		}

		if (in_array($data['page'], $this->page_lang_codes)) {
			$this->page_lang_code = $data['page'];
		} else{
			exit("Invalid Page Lang Code");
		}

		if (in_array($data['mail'], $this->mail_lang_codes)) {
			$this->mail_lang_code = $data['mail'];
		} else{
			exit("Invalid Mail Lang Code");
		}
	}
	
	public function init()
	{
		$currency_code = (isset($this->currency_code) ? $this->currency_code : 'TRY');
		$page_lang = (isset($this->config['page_lang']) ? $this->config['page_lang'] : 'TR');
		$mail_lang = (isset($this->config['mail_lang']) ? $this->config['mail_lang'] : 'TR');
		
		if ($this->config['payment_method'] == 'cc') {
			$result = $this->Curl('credit_card', [
				"usrIp" => $this->GetIP(),
				"usrName" => $this->customer['name'],
				"usrAddress" => $this->customer['address'],
				"usrPhone" => $this->customer['phone'],
				"usrEmail" => $this->customer['email'],
				"amount" => $this->product['amount'],
				"returnID" => uniqid() . 'SHIPY' . $this->product['order_id'],
				"currency" => $currency_code,
				"pageLang" => $page_lang,
				"mailLang" => $mail_lang,
				"installment" => $this->product['installment'],
				"apiKey" => $this->config['api_key']
			]);
		} elseif ($this->config['payment_method'] == 'mobile') {
			$result = $this->Curl('mobile', [
				"usrIp" => $this->GetIP(),
				"usrName" => $this->customer['name'],
				"usrAddress" => $this->customer['address'],
				"usrPhone" => $this->customer['phone'],
				"usrEmail" => $this->customer['email'],
				"amount" => $this->product['amount'],
				"returnID" => uniqid() . 'SHIPY' . $this->product['order_id'],
				"apiKey" => $this->config['api_key']
			]);
		}

		if ($result['status'] != 'success') {
			exit('Payment failed! <br> Reason: ' . $result['message']);
		} else{
			return $result['link'];
		}
	}

	public function callback()
	{
		$return_id = $_POST['returnID'];
		$payment_id = $_POST['paymentID'];
		$payment_type = $_POST['paymentType'];
		$payment_amount = $_POST['paymentAmount'];
		$payment_currency = $_POST['paymentCurrency'];
		$payment_hash = $_POST['paymentHash'];

		$order_id = explode('SHIPY', $return_id);

		if ($this->GetIP() != "144.91.111.2") exit();

		if ($return_id == null || $payment_id == null || $payment_type == null || $payment_amount == null || $payment_currency == null || $payment_hash == null) {
			exit("Missing value sent.");
		} else{

			$hashbytes = mb_convert_encoding(($payment_id . $return_id . $payment_type . $payment_amount . $payment_currency . $this->config['api_key']), "ISO-8859-9");
			$hash = base64_encode(sha1($hashbytes, true));

			if($hash != $payment_hash) {
				exit('Shipy failed: bad hash');
			} else{
				
				return [
					'return_id' => $return_id,
					'payment_id' => $payment_id,
					'payment_type' => $payment_type,
					'payment_amount' => $payment_amount,
					'payment_currency' => $payment_currency,
					'payment_hash' => $payment_hash,
					'order_id' => $order_id[1]
				];
			}
		}
	}

	public function Curl($type, $data=[])
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.shipy.dev/pay/" . $type,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data
		]);
		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			exit('Shipy Connection Error! <br> Error: ' . curl_error($curl));
		} else{
			return json_decode($response, true);
		}
		curl_close($curl);
	}

	public function GetIP()
	{
		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			if (strstr($ip, ',')) {
				$tmp = explode (',', $ip);
				$ip = trim($tmp[0]);
			}
		} else{
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}
}