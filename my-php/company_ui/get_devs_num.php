<?php
	session_start();
	
	$_SESSION['m_code'] = 'xx';

	if( !isset($_SESSION['m_code'])  )
		exit;
	
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
	$con = touch_mysql();
	if( empty($con) )
		exit;
	
	$sql_str = "SELECT count(*) FROM dev_db.dev_store WHERE m_code='".$_SESSION['m_code']."'";
	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return;
	
	$row = mysql_fetch_array( $res );
	$devs_num = $row[0];
	mysql_free_result ( $res );
	

	mysql_close( $con );
	
	echo $devs_num;
	
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