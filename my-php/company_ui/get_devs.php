<?php
	session_start();
	
	//$_SESSION['m_code'] = 'xx';

	if( !isset($_SESSION['m_code'])  )
		exit;
	
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
	$con = touch_mysql();
	if( empty($con) )
		exit;
	
	$sql_str = "SELECT name, guid1, model, state FROM dev_db.dev_store WHERE m_code='".$_SESSION['m_code']."'";
	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return;
	
	$json_str = '{"devs":[';

	while( $row=mysql_fetch_array( $res ) )
		$json_str .= '{"n":"'.$row[0].'","g1":"'.$row[1].'","m":"'.$row[2].'","s":"'.$row[3].'"},';
	
	$json_str = rtrim( $json_str, ',' );
	$json_str .= ']}';
	
	mysql_free_result ( $res );
	

	mysql_close( $con );
	
	echo $json_str;
	
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