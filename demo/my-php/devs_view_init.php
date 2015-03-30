<?php
/*
	<xml>
		<dev>
			<g1></g1>
			<s>state</s>
			<n>name</n>
			<tz>timezone</tz>
			<lt>最后更新时间</lt>
		</dev>
		<dev>
			.............
		</dev>
	</xml>
*/
	require_once( 'codec_lib.php' );
	
	$config = read_config( '../../config/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	
	$xml = '<xml>';
	
	$res1 = mysql_query( "SELECT DISTINCT guid1,state,name,timezone FROM dev_db.dev_table WHERE owner='demo'", $con );
	while( $row1 = mysql_fetch_array( $res1 ) ) {

		$xml .= '<dev><g1>'.$row1[0].'</g1>';
		$xml .= '<s>'.$row1[1].'</s>';
		$xml .= '<n>'.$row1[2].'</n>';
		$xml .= '<tz>'.$row1[3].'</tz>';
		
		$sql_str = sprintf("SELECT max(time) FROM data_db.his_data WHERE dev_id='%s'", $row1[0] );
		$res2 = mysql_query( $sql_str, $con );
		$t1 = mysql_fetch_array( $res2 );
		mysql_free_result( $res2 );

		$sql_str = sprintf("SELECT max(time) FROM data_db.real_data WHERE dev_id='%s'", $row1[0] );
		$res2 = mysql_query( $sql_str, $con );
		$t2 = mysql_fetch_array( $res2 );
		mysql_free_result( $res2 );
		
		$array = array( floatval($t1[0]), floatval($t2[0]) );
		$lt = max( $array );
		if ( empty($lt) )
			$lt = time();						// 如数据最近上传时间为空，或为0，则为 N
		
		$xml .= '<lt>'.$lt.'</lt>';
		$xml .= '</dev>';
	}
	
	mysql_free_result( $res1 );
	mysql_close($con);
	
	$xml .= '</xml>';
	
	echo $xml."\n"; 
	
?>