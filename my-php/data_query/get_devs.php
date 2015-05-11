<?php
	// 根据 dev 的 guid1，返回此设备的所有参数名称 v_name 和 d_id
/*
<xml>
	<d i=g1>**</d>
	<d i=g1>**</d>
	<d i=g1>**</d>
</xml>
*/	

	session_start();
	
	//$_SESSION['user'] = 'free-bug@163.com';
	
	if( !isset($_SESSION['user']) )
		exit;
		
	require_once( "../php-lib/codec_lib.php" );

	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;

	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );
	mysql_query("SET NAMES 'utf8'", $con);
	
	$xml .= get_dev_own_by_user( $_SESSION['user'], $con );
	
	mysql_close( $con );
	
	$xml .= '</xml>';
	echo $xml;
	
//---------------------------------------------------------------------------------------
	function get_dev_own_by_user( $user, $con ) {
			
		$x_str = '';
		$res = mysql_query( "SELECT name, guid1 FROM dev_db.dev_table WHERE owner='$user'", $con );
		if( empty($res) )
			return '';
		
		while( $row=mysql_fetch_array($res) )
			$x_str .= "<d i=$row[1]>$row[0]</d>";
		
		return $x_str;
	}
?>