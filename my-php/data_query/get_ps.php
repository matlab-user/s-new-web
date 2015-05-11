<?php
	// 根据 guid1，返回此设备下的所有参数名称 v_name 和 d_id
/*
<xml>
	<p i=d_id>**</p>
	<p i=d_id>**</p>
</xml>
*/	

	session_start();
	
	//$_SESSION['user'] = 'free-bug@163.com';
	//$_POST['g1'] = 'TFwqovfw';
	
	if( !isset($_SESSION['user']) || !isset($_POST['g1']) )
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
	
	$xml .= get_ps( $_POST['g1'], $con );
	
	mysql_close( $con );
	
	$xml .= '</xml>';
	echo $xml;
	
//---------------------------------------------------------------------------------------
	function get_ps( $g1, $con ) {
			
		$x_str = '';
		$res = mysql_query( "SELECT v_name, d_id FROM data_db.dev_data_unit WHERE dev_id='$g1'", $con );
		if( empty($res) )
			return '';
		
		while( $row=mysql_fetch_array($res) )
			$x_str .= "<p i=$row[1]>$row[0]</p>";
		
		return $x_str;
	}
?>