<?php

class Rockform extends Events  {

	protected $config, $lang, $field; 

	var $tmp_form_popup = 'form_popup.html';
	var $tmp_report_on_mail = 'report_on_mail.html';

	function __construct($config = array(), $lang = array()) {
		$this->config = $this->set_default_config($config, $lang);
		$this->lang = $lang;
	}

	private function set_default_config($config, $lang) {

 		if(empty($config['subject'])) {
 			$config['subject'] = $lang['main']['subject'];
 		}

 		if(empty($config['from_email'])) {
 			$config['from_email'] = $lang['main']['from_email'];
 		}

 		if(empty($config['from_name'])) {
 			$config['from_name'] = $lang['main']['from_name'];
 		}

		return $config;
	}

	public function init() {
 
		$type = isset($_REQUEST['type']) ? preg_replace ("/[^a-z]/i","", $_REQUEST['type']) : 'default';

		$out = array();  

 		switch ($type) {
 			case 'capcha':

 			return $this->set_capcha();

			case 'form':
    		 	$out = $this->set_base_form();
    		break; 

    		case 'validation':
    			$out = $this->set_json_encode($this->validation());
    		break;
			
			default:  
				$out = $this->set_json_encode($this->set_form_data());
			break;
		} 
		return $out;
	}

	function set_capcha() {
		include('backend/lib/kcaptcha/kcaptcha.php');

		$captcha = new KCAPTCHA();
		$_SESSION['captcha_keystring'] = $captcha->getKeyString();
	}

	private function validation() {
		return $this->set_token();
	}
 
	private function set_token() {
		$bf_token = md5(uniqid());
		$_SESSION['bf-token'] = $bf_token;
		return array('token' => $bf_token);
	}

	private function set_form_data() {

		$out = array();
 		$field = array();
 		 
		foreach ($_POST as $key => $value) {
			if(is_array($value)) {
				$field[$key] = implode(', ',$value);
			} else {
				$field[$key] = $value;
			}
		}
		$this->field = $field;

		$error = $this->check_error();
		if(empty($error)) {

			$this->before_success_send_form($field);

			$to = $this->config['to'];

			if($this->config['mail_send'] > 0) {
				if(!empty($to)) {
					$body = $this->set_report_form();

					if($this->set_mail($body)) {
						$out = $this->set_form_data_status(1, $this->lang['main']['success_email_send']);
					} else {
						$out = $this->set_form_data_status(0, $this->lang['err']['email_send']);
					}

				} else {
					$out = $this->set_form_data_status(0, $this->lang['err']['not_email']);
				}
			} else {
				$out = $this->set_form_data_status(1, $this->lang['main']['success_email_send']);
			}

			$this->after_success_send_form();

 		} else {
 			$out = $error;
 		}

		return $out;
	}

	private function set_report_form() {
 
		Twig_Autoloader::register(true);
		$loader = new Twig_Loader_Filesystem('configs/'.$this->config['name'].'/templates/');
		$twig = new Twig_Environment($loader);
		return $twig->render($this->tmp_report_on_mail, $this->field);

	}

	private function set_base_form() {
 
		Twig_Autoloader::register(true);
		$loader = new Twig_Loader_Filesystem('configs/'.$this->config['name'].'/templates/');
		$twig = new Twig_Environment($loader);
		return $twig->render($this->tmp_form_popup, $_POST['attributes']);
 
	}

	private function set_form_data_status($status = 0, $value = '') {
		return array('status' => $status, 'value' => $value);
	}

	private function check_error() {

		$out = '';

		$error_types = $this->config['error_type'];

		if(!empty($error_types)) {

			$error_types = explode(',',$error_types);

			foreach ($error_types as $type) {

				$type = trim($type);

				$err = '';

				switch ($type) {

					case 'capcha':
    					$err = $this->check_capcha();
    				break;

					case 'spam':
    					$err = $this->check_spam(); 
    				break; 

					default: break;
				} 

				if(!empty($err)) {
					$out = $err;
					break;
				}
			}
		}

		return $out;
	}

	private function check_capcha() {
		$out = '';

		$_SESSION['captcha_keystring'] = isset($_SESSION['captcha_keystring']) ? $_SESSION['captcha_keystring'] : '';
		
		if(strcmp($_SESSION['captcha_keystring'], $this->field['capcha']) == 0){
			
		} else {
			 $out = $this->set_form_data_status(0, $this->lang['err']['capcha']);
		}
		return $out;
	}

	private function check_spam() {
		$out = '';

		if($this->checked_ajax()) { 
		} else {
			$out = $this->set_form_data_status(0, $this->lang['err']['spam']);
		}

		$token = isset($_POST['bf-token']) ? $_POST['bf-token'] : '';
		$_SESSION['bf-token'] = isset($_SESSION['bf-token']) ? $_SESSION['bf-token'] : 0;

		if(!empty($token) && (strcmp($_SESSION['bf-token'], $token) == 0)) {
			
		} else {
			$out = $this->set_form_data_status(0, $this->lang['err']['spam']);
		}

		return $out;
	}

	private function checked_ajax() {
  		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  		 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	private function check_required_field() {
		$out = '';

		if(!empty($this->config['required_field'])) {
			$this->config['required_field'] = explode(',', $this->config['required_field']);

			foreach ($this->config['required_field'] as $value) {
				$current = isset($_POST[trim($value)]) ? trim($_POST[trim($value)]) : '';
				if(!isset($_POST[trim($value)]) || empty($current)) {
					$out = $this->set_form_data_status(0, $this->lang['err']['required_field']);
				}
			} 
		}
		return $out;
	}

	protected function set_mail($body = '') {

		$mail = new PHPMailer();

		$mail->CharSet = 'utf-8';

		$mail->From = $this->config['from_email'];
		$mail->FromName = $this->config['from_name'];

		$mail->isHTML(true);  
		$mail->Subject = $this->config['subject'];
		$mail->Body    = $body;
		$mail->AltBody = strip_tags($body);
 		
 		//set files

		foreach ($_FILES as $name_upload_file => $files) {
			if(isset($_FILES[$name_upload_file]["name"])) {
				$files_count = sizeof($_FILES[$name_upload_file]["name"]);
				for ($i = 0; $i <= $files_count - 1; $i++) {	
					if (isset($_FILES[$name_upload_file]) && $_FILES[$name_upload_file]['error'][$i] == UPLOAD_ERR_OK) {
    					$mail->AddAttachment(
    						$_FILES[$name_upload_file]['tmp_name'][$i],
    						$_FILES[$name_upload_file]['name'][$i],
    						'base64',
    						$_FILES[$name_upload_file]['type'][$i]
    					);
					}
				}
			}
		}

 		$to = $this->config['to'];
		if(!is_array($to)) {
			$to = explode(',', $to);
		} 

		foreach((array)$to as $email) {
			//Recipients will know all of the addresses that have received a letter
			$mail->addAddress($email, '');
		}

		if($this->config['SMTPAuth']) {
 			//$mail->SMTPDebug = 3;

			$mail->isSMTP(); // Set mailer to use SMTP
			$mail->Host = $this->config['Host'];  // Specify main and backup SMTP servers
			$mail->SMTPAuth = $this->config['SMTPAuth']; // Enable SMTP authentication
			$mail->Username = $this->config['Username']; // SMTP username
			$mail->Password = $this->config['Password']; // SMTP password
			$mail->SMTPSecure = $this->config['SMTPSecure']; // Enable TLS encryption, `ssl` also accepted                           
			$mail->Port = $this->config['Port']; // TCP port to connect to
		}

		return $mail->send();
	}

	protected function set_json_encode($value) {
		$out = '';
		if (function_exists('json_encode')) {  
			$out = json_encode($value);	
		} 
		header('Content-type: text/json;  charset=utf-8');
		header('Content-type: application/json');
		return $out;
	}
}
