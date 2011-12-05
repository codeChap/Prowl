<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Prowl;

class DidNotValidate extends \FuelException {}
class DidNotAuthorize extends \FuelException {}
class OverLimit extends \FuelException {}
class NotApproved extends \FuelException {}
class InternalError extends \FuelException {}

/**
 * Prowl
 *
 * @package     Fuel
 * @subpackage  Prowl
 */
class Prowl {

	static $key = false;
	static $application = false;
	static $priority = 0;
	static $subject = false;
	static $message = false;
	static $action = false;

	static $url = "https://api.prowlapp.com/publicapi/add";
	static $error = false;

	public static function forge(array $options=array())
	{
		// Setup config
		if(is_array($options)){
			$class_variables = get_class_vars("Prowl");
			foreach($options as $k => $v){
				if(array_key_exists($k, $class_variables)){
					self::${$k} = $v;
				}
			}
		}
		return new \Prowl($options);
	}

	public static function set_application($application)
	{
		self::$application=utf8_encode($application);
	}
	
	public static function set_priority($priority)
	{
		$options = array("-2", "-1", "0", "1", "2");
		
		if(in_array($priority, $options)){
			self::$priority=$priority;
		}else{
			self::$error[] = "Priority not valid.";
		}
	}
	
	public static function set_action($action)
	{
		if($action){
			// Check valid URL and set it.
			if(filter_var($action, FILTER_VALIDATE_URL)) {
			    self::$action=$action;
			}
			else{
			    self::$error[] = "Action url not valid.";
			}
		}
	}
	
	public static function set_subject($subject)
	{
		self::$subject=$subject;
	}
	
	public static function set_message($message)
	{
		// Cleanup html if any
		$message = trim(strip_tags(preg_replace("/<br\/?>/", "\n", utf8_encode($message))));
		
		// Check str length
		if(strlen($message)<=10000){
			self::$message=$message;
		}
		else{
			self::$error[] = "Message exceeds maximum length of 1000 characters.";
		}
	}

	public function add_key($key)
	{
		if(is_array($key)){
			$keys = FALSE;
			foreach($key as $k => $v){
				if(strlen($v) == 40){
					$keys .= $v.",";
				}else{
					self::$error[] = "Invalid api key ( {$v} ).";
				}
			}
			self::$key = rtrim($keys,",");
		}else{
			if(strlen($key) == 40){
				self::$key = $key;
			}
			else{
				self::$error[] = "Invalid api key ( {$key} ).";
			}
		}
	}

	public static function send()
	{
		self::push();
	}

	public static function post()
	{
		self::push();
	}
	
	public static function push()
	{
		// Check required fields
		if(!self::$key){			self::$error[] = "Api key not set in config.";}
		if(!self::$application){	self::$error[] = "Application name not set.";}
		if(!self::$subject){		self::$error[] = "Subject not set.";}
		if(!self::$message){		self::$error[] = "Description not set.";}

		if(is_array(self::$error) && count(self::$error)>0){
			$error = implode("<br/>", self::$error);
			die($error);
		}
		
		// All good - continue
		$fields = array(
			'apikey'=>self::$key,
			'url'=>self::$action,
			'priority'=>self::$priority,
			'application'=>urlencode(self::$application),
			'event'=>urlencode(self::$subject),
			'description'=>urlencode(self::$message)
		);
		
		// Url-ify the data for the POST
		$fields_string = FALSE;
		foreach($fields as $key=>$value){
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string,'&');
		
		// Curl
		$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,self::$url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			
			$return = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
		curl_close($ch);

		switch($httpcode)
		{
			case 400 :
				throw new DidNotValidate('Bad request, the parameters you provided did not validate.');
			break;
			
			case 401 :
				throw new DidNotAuthorize('Not authorized, the API key given is not valid, and does not correspond to a user.');
			break;
			
			case 406 :
				throw new OverLimit('Not acceptable, your IP address has exceeded the API limit.');
			break;
			
			case 409 :
				throw new NotApproved('Not approved, the user has yet to approve your retrieve request.');
			break;
			
			case 500 :
				throw new InternalError('Internal server error, something failed to execute properly on the Prowl side.');
			break;
			
			case 200 :
			default:
				return true;
			break;
		}
	}
}

/* end of file prowl.php */