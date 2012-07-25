<?php

class Google{

	public $client_id;
	public $client_secret;

	public $accessToken;

	public $error;

  	public static $CURL_OPTS = array(
    	CURLOPT_CONNECTTIMEOUT => 10,
    	CURLOPT_RETURNTRANSFER => true,
    	CURLOPT_TIMEOUT        => 60,
	);

	public static $DOMAIN_MAP = array(
	    'auth'      => 'https://accounts.google.com/o/oauth2/auth',
	    'token'		=> 'https://accounts.google.com/o/oauth2/token',
		'www'       => 'https://www.google.com/',
	);

	public function __construct($client_id, $client_secret){
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}

	public function authorize($callback){
		if($this->getAccessToken($callback)){
			return true;
		}else{
			return false;
		}
	}

	public function getCurrentUser($callback){
		if($this->authorize($callback)){
			return $this->get('userinfo')
		}else{
			return false;
		}
	}

	public function getLoginURL($callback, $scopes = array(), $response_type = 'code', $approval_prompt = 'auto', $access_type = 'offline'){

		$url_fragments = array(
			'redirect_uri='.$callback,
			'client_id='.$this->client_id,
			'approval_prompt='.$approval_prompt,
			'response_type='.$response_type,
			'access_type='.$access_type
		);

		if(sizeof($scopes) > 0){
			$url_scopes = 'scope=';
			foreach($scopes as $scope){
				$url_scopes .= $this->getScopeURI($scope).'+';
			}
			$url_fragments[] = substr($url_scopes, 0, strlen($url_scopes)-1);
		}
		return self::$DOMAIN_MAP['auth'].'?'.implode('&amp;', $url_fragments);
	}

	public function setAccessToken($accessToken){
		$this->accessToken = $accessToken;
	}

	public function getAccessToken($redirect_uri = null){
		if(empty($this->accessToken)){
			$accessToken = $this->post(self::$DOMAIN_MAP['token'], array(
				'code' => $_REQUEST['code'],
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri' => $redirect_uri,
				'grant_type' => 'authorization_code'
			));
			if(isset($accessToken->access_token)
				$this->accessToken = $accessToken->access_token;
			else{
				$this->accessToken = false;
				$this->error = $accessToken->error;
			}
		}
		return $this->accessToken;
	}

	public function post($url, $params = array(), $ch = null){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		$headers = array( "Expect:" );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt_array($ch, self::$CURL_OPTS);

		$post_string = '';
		foreach($params as $key => $value){
			$post_string .= $key.'='.$value.'&';
		}
		$post_string = substr($post_string, 0, strlen($post_string)-1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$data = curl_exec($ch);
		curl_close($ch);

		return json_decode($data);
	}

	public function get($url, $params = array(), $ch = null){
		$ch = curl_init();

		$get_string = 'access_token='.$this->getAccessToken();
		if(sizeof($params) > 0){
			foreach($params as $key => $value){
				$get_string .= $key.'='.$value.'&';
			}
			$get_string = substr($get_string, 0, strlen($get_string)-1);
		}

		curl_setopt($ch, CURLOPT_URL, $this->getAPI_URI($url).'?'.$get_string);

		$headers = array( "Expect:" );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt_array($ch, self::$CURL_OPTS);
		$data = curl_exec($ch);
		curl_close($ch);

		return json_decode($data);
	}

	public function getScopeURI($alias){

		$API_URLS = array(
			'email' 	=> 'https://www.googleapis.com/auth/userinfo.email',
			'profile' 	=> 'https://www.googleapis.com/auth/userinfo.profile',
		);

		return $API_URLS[$alias];
	}

	public function getAPI_URI($alias){

		$API_URIS = array(
			'plus/me' => 'https://www.googleapis.com/plus/v1/people/me',
			'userinfo' => 'https://www.googleapis.com/oauth2/v2/userinfo'
		);
		return isset($API_URIS[$alias]) ? $API_URIS[$alias] : $alias;
	}

	function getLastError(){
		return $this->error;
	}
}