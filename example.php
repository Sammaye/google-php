<?php
require_once 'Google.php';

$google = new Google('client_id', 'client_secret');

if(isset($_REQUEST['code'])){ // Then we are in stage two
	if($google->authorize('http://x.co.uk/example.php')){
		var_dump($google->get('userinfo'));
	}else{
		echo "something went wrong";
	}

	/**
	 * Output:

		object(stdClass)[54]
		  public 'id' => string '669' (length=21)
		  public 'email' => string 'hell@gmail.com' (length=21)
		  public 'verified_email' => boolean true
		  public 'name' => string 'Satan' (length=11)
		  public 'given_name' => string 'Satan' (length=3)
		  public 'family_name' => string 'Hell' (length=7)
		  public 'link' => string 'https://plus.google.com/666' (length=45)
		  public 'gender' => string 'male/unknown' (length=4)
		  public 'locale' => string 'en-GB' (length=5)
	 */

}

echo '<a href="'.$google->getLoginURL('http://x.co.uk/example.php', array('email', 'profile')).'">login</a>';


