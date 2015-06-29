<?php
	session_start();
	
	if( !isset($_POST['load']) )
		exit;

	require_once( "../php-lib/codec_lib.php" );
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
    $key = pack( 'H*', $config->key_1 );
    
	$iv_size = 16;
	
    $ciphertext_dec = base64_decode( $_POST['load'] );
    $iv_dec = substr( $ciphertext_dec, 0, $iv_size );
    $ciphertext_dec = substr( $ciphertext_dec, $iv_size );

    $plaintext_dec = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec );
	
	$mid = strpos( $plaintext_dec, "\0" );
	$plaintext_dec = substr( $plaintext_dec, 0, $mid );
	
	$load_obj = json_decode( $plaintext_dec );
	
	if( isset($load_obj->A) ) {			// 注册用户
		
		$sig = 'NO';
		$sql_con = touch_mysql();
		if( empty($sql_con) )
			exit;
		
		$load_obj->A = mysql_real_escape_string( $load_obj->A );
		$query_str = "SELECT passwd,state,uid,dnum,type FROM user_table WHERE mail='".$load_obj->A."'";
		$res = mysql_query( $query_str, $sql_con );
		while( $row=mysql_fetch_array($res) ) {
			if( $row[1]=='inactive' )
				break;
			
			$_SESSION['user'] = $load_obj->A;
			$_SESSION['dnum'] = $row[3];
			$_SESSION['uid'] = $row[2];
			$_SESSION['l_login'] = time();
			$sig = 'OK';
		}
		mysql_close( $sql_con );
		echo $sig;
	}
	
//-----------------------------------------------------------------------------
function touch_mysql() {
	global $mysql_user, $mysql_pass;
	$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
	if( !$con )
		die( 'Could not connect: ' . mysql_error() );	
	mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
	mysql_select_db( 'user_db', $con );
	return $con;
}	
?>