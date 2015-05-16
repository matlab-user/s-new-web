<?php
	// 获取指定设备的所有数据
	//$_POST['g1'] = '1982011602030410182910a1F2C3D02A';
	
	if( !isset($_POST['g1']) )
		exit;
	
	require_once( "./php-lib/codec_lib.php" );
	$config = read_config( './php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;	
	
	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	
	$res = mysql_query( "SELECT guid1, name, model, maker, intv, timezone, longitude, latitude FROM dev_db.dev_table WHERE guid1='".$_POST['g1']."'", $con );
	$row = mysql_fetch_array( $res );
	
	$xml .= '<name>'.$row[1].'</name>';
	$xml .= '<model>'.$row[2].'</model>';
	$xml .= '<maker>'.$row[3].'</maker>';
	$xml .= '<intv>'.$row[4].'</intv>';
	$xml .= '<tz>'.$row[5].'</tz>';	
	$xml .= '<longitude>'.$row[6].'</longitude>';
	$xml .= '<latitude>'.$row[7].'</latitude>';
	
	mysql_free_result ( $res );
	
	mysql_close( $con );
	
	$xml .= '</xml>';
	echo $xml."\r\n";
?>