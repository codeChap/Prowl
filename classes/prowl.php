<?php
/**
 * Fuel Prowl Package
 *
 * @copyright  2012 Derrick Egersdorfer
 * @license    MIT License
 */

namespace Prowl;

class DidNotValidate extends \FuelException {}
class DidNotAuthorize extends \FuelException {}
class OverLimit extends \FuelException {}
class NotApproved extends \FuelException {}
class InternalError extends \FuelException {}
class ConfigError extends \FuelException {}

class Prowl {

	private static $key = false;
	private static $application = false;
	private static $priority = 0;
	private static $subject = false;
	private static $message = false;
	private static $action = false;
	private static $url = false;
	private static $error = false;

	/**
	 * Sets initial configuration and credentials to talk to prowl server.
	 */
	private static function configuration($options=array())
	{
		// Merge with config
		$config = \Arr::merge( $options, \Config::get('prowl') );

		// Setup credentials
		if(is_array($config)){
			$class_variables = get_class_vars("Prowl");
			foreach($config as $k => $v){
				if(array_key_exists($k, $class_variables)){
					self::${$k} = $v;
				}
			}
		}
		else{
			throw new ConfigError($error);
		}
	}

	/**
	 * Init, config loading.
	 */
	public static function _init()
	{
		\Config::load('prowl', true);

		// Set up configuration
		$configuration = self::configuration();
	}

	/**
	 * Forge new Prowl instance
	 */
	public static function forge(array $config=array())
	{
		// Set up configuration
		if( ! self::$key){
			$configuration = self::configuration($config);
		}

		// Done
		return new \Prowl($configuration);
	}

	/**
	 * Sets the application name.
	 */
	public static function application($application)
	{
		self::$application=utf8_encode($application);
	}

	/**
	 * Sets the priority.
	 */
	public static function priority($priority)
	{
		$options = array("-2", "-1", "0", "1", "2");

		if(in_array($priority, $options)){
			self::$priority=$priority;
		}else{
			self::$error[] = "Priority not valid.";
		}
	}

	/**
	 * Sets a url action to be called from prowl.
	 */
	public static function action($action)
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

	/**
	 * Sets the subject.
	 */
	public static function subject($subject)
	{
		self::$subject=$subject;
	}

	/**
	 * Sets the message.
	 */
	public static function message($message)
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

	/**
	 * Sets or ads in keys to message to.
	 */
	public static function key($key)
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

	/**
	 * Alias for push
	 */
	public static function send($message = false, $config = false)
	{
		self::push();
	}

	/**
	 * Alias for push
	 */
	public static function post($message = false, $config = false)
	{
		self::push();
	}

	/**
	 * Pushes the message to your iphone etc
	 */
	public static function push($data = false)
	{
		// Arrays are treated as configs otherwise the message to send
		if($data){
			if(is_array($data)){
				self::configuration($data);
			}
			else{
				self::message($data);
			}
		}

		// Check required fields
		if( ! self::$key){			self::$error[] = "api key not set";}
		if( ! self::$application){	self::$error[] = "application not set";}
		if( ! self::$subject){		self::$error[] = "subject not set";}
		if( ! self::$message){		self::$error[] = "description not set";}

		if(is_array(self::$error) && count(self::$error)>0){
			$error = implode(", ", self::$error);
			throw new ConfigError($error);
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
