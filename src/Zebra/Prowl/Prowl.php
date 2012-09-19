<?php

/**
 * PHP library to access the Prowl service.
 *
 * @author     Derrick Egersdorfer
 * @license    MIT License
 * @copyright  2011 - 2012 Derrick Egersdorfer
 */

class DidNotValidate extends \Exception {}
class DidNotAuthorize extends \Exception {}
class OverLimit extends \Exception {}
class NotApproved extends \Exception {}
class InternalError extends \Exception {}
class ConfigError extends \Exception {}

class Prowl {

	private $action = false;
	private $application = false;
	private $debug = false;
	private $failOnNotAuthorized = false;
	private $key = false;
	private $message = false;
	private $priority = 0;
	private $url = 'https://api.prowlapp.com/publicapi/add';
	private $subject = false;

	private $error = false;

	/**
	 * Forge new Prowl instance
	 */
	public function __construct(array $config = array())
	{
		// Get all variables of this class
		$params = array_keys( get_class_vars( get_called_class() ) );

		// Loop and set config values
		foreach( $params as $key ){
			if(array_key_exists($key, $config)){
				if( ! call_user_func(array($this, 'set'.ucFirst(strtolower($key))), $config[$key]))
				{
					throw new ConfigError("Could not set $key to " . $config[$key] . ": " . implode($this->error) );
				}
			}
		}

		// Done
		return true;
	}

	/**
	 * Sets a url action to be called from prowl.
	 */
	public function setAction($action=false)
	{
		if($action){
			// Check valid URL and set it.
			if(filter_var($action, FILTER_VALIDATE_URL)) {
			    $this->action=$action;
			}
			else{
			    $this->error[] = "action url not valid";
			    return false;
			}
		}

		// Done
		return true;
	}

	/**
	 * Sets the application name.
	 */
	public function setApplication($application=false)
	{
		if($application){
			$this->application=utf8_encode($application);
		}
		else{
			return false;
		}

		// Done
		return true;
	}

	/**
	 * Turn debuggin on
	 */
	public function setDebug($debug=false)
	{
		if($debug){
			$this->debug = true;
		}

		return true;
	}

	/**
	 * Should we fail without an error when the api key is invalid
	 */
	public function setFailOnNotAuthorized($fail = false)
	{
		if($fail){
			$this->failOnNotAuthorized = true;
		}

		return true;
	}

	/**
	 * Sets or ads in keys to message to.
	 */
	public function setKey($key)
	{
		// Sets an array of keys
		if(is_array($key)){
			$keys = FALSE;
			foreach($key as $k => $v){
				if(strlen($v) == 40){
					$keys .= $v.",";
				}else{
					$this->error[] = "invalid api key ( {$v} )";
					return false;
				}
			}
			$this->key = rtrim($keys,",");
		}else{

		// Sets a single key
			if(strlen($key) == 40){
				$this->key = $key;
			}
			else{
				$this->error[] = "invalid api key ( {$key} )";
				return false;
			}
		}

		// Done
		return true;
	}

	/**
	 * Sets the message.
	 */
	public function setMessage($message)
	{
		// Cleanup html if any
		$message = trim(strip_tags(preg_replace("/<br\/?>/", "\n", utf8_encode($message))));

		// Check str length
		if(strlen($message)<=10000){
			$this->message=$message;
		}
		else{
			$this->error[] = "message exceeds maximum length of 1000 characters";
			return false;
		}

		// Done
		return true;
	}

	/**
	 * Sets the priority.
	 */
	public function setPriority($priority)
	{
		// Valiud options
		$options = array("-2", "-1", "0", "1", "2");

		if(in_array($priority, $options)){
			$this->priority=$priority;
		}else{
			$this->error[] = "priority not valid, use:".implode(", ", $options);
			return false;
		}

		// Done
		return true;
	}

	/**
	 * Sets a url action to be called from prowl.
	 */
	public function setUrl($url=false)
	{
		if($url){
			// Check valid URL and set it.
			if(filter_var($url, FILTER_VALIDATE_URL)) {
			   	$this->url=$url;
			}
			else{
			    $this->error[] = "action url not valid";
			    return false;
			}
		}

		// Done
		return true;
	}

	/**
	 * Sets the subject.
	 */
	public function setSubject($subject=false)
	{
		if($subject){
			$this->subject=$subject;
		}
		else{
			$this->error[] = "empty subject";
			return false;
		}

		// Done
		return true;
	}

	/**
	 * Alias for push
	 */
	public function send($message = false)
	{
		self::push();
	}

	/**
	 * Alias for push
	 */
	public function post($message = false)
	{
		self::push();
	}

	/**
	 * Pushes the message to your iphone etc
	 */
	public function push($message = false)
	{
		// Arrays are treated as configs otherwise the message to send
		if($message){
			if( ! $this->setMessage($message)){
				throw new ConfigError(implode($this->error));
			}
		}

		// Check required fields
		if( ! $this->application )	{ $this->error[] = "application not set"; }
		if( ! $this->key )			{ $this->error[] = "key not set"; }
		if( ! $this->message )		{ $this->error[] = "message not set"; }
		if( ! $this->url )			{ $this->error[] = "url not set"; };
		if( ! $this->subject ) 		{ $this->error[] = "subject not set"; };

		if(is_array($this->error) && count($this->error)>0){
			$error = implode(", ".PHP_EOL, $this->error) . PHP_EOL;
			throw new ConfigError($error);
		}

		// All good - continue
		$fields = array(
			'apikey'=>$this->key,
			'url'=> $this->action,
			'priority'=>$this->priority,
			'application'=>urlencode($this->application),
			'event'=>urlencode($this->subject),
			'description'=>urlencode($this->message)
		);

		// Url-ify the data for the POST
		$fields_string = FALSE;
		foreach($fields as $key=>$value){
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string,'&');

		// Curl
		$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$this->url);
			curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
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
			case 401 :
				if($this->failOnNotAuthorized == true){
					throw new DidNotAuthorize('Not authorized, the API key given is not valid, and does not correspond to a user.');
				}
				else{
					return true;
				}
			break;

			case 400 :
				throw new DidNotValidate('Bad request, the parameters you provided did not validate.');
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
				return true;
			break;

			default:
				throw new Exception('An unknown error occured. ' . $return);
			break;
		}
	}
}
