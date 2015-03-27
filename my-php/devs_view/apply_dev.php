<?php
	session_start();
	
	$_SESSION['uid'] = '12345678';
	$_SESSION['dnum'] = 5;
	
	if( !isset($_SESSION['uid']) | !isset($_SESSION['dnum']) )
		exit;
	
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;

	$sql_con = touch_mysql();
	if( empty($sql_con) )
		exit;
	
	// 查询当前用户已经拥有多少台设备
	$dev_num = 0;
	$query_str = "SELECT COUNT(guid1) FROM dev_db.dev_table WHERE owner='".$_SESSION['uid']."'";
	$res = mysql_query( $query_str, $sql_con );
	while( $row=mysql_fetch_array($res) )
		$dev_num = intval( $row[0] );
	mysql_free_result( $res );

	if( $dev_num>=$_SESSION['dnum'] ) {
		mysql_close( $sql_con );
		echo 'NO';
		exit;
	}
	
	$query_str = "SET @guid = ''";
	mysql_unbuffered_query( $query_str, $sql_con );
	$query_str = "CALL apply_dev_guid(8,'".$_SESSION['uid']."',@guid)";
	mysql_unbuffered_query( $query_str, $sql_con );
	
	$query_str = 'SELECT @guid';
	$res = mysql_query( $query_str, $sql_con );
	while( $row=mysql_fetch_array($res) )
		$new_guid = $row[0];

	mysql_close( $sql_con );

	echo $new_guid;
	
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