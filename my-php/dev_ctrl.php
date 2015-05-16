<?php
// $_POST['cmd'] - 控制指令，字符串
// 指令形式为：[dev1_guid,(op1,p1,p2…)]
	
//	$_POST['g1'] = 'TFwqovfw';
//	$_POST['cmd'] = 'wdh--------';

	set_time_limit( 10 );
	
	if( !isset($_POST['g1']) )
		exit;
	
	// 从数据库中获取设备控制 ip、port
	require_once( "./php-lib/codec_lib.php" );
	
	$config = read_config( './php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;	
	
	$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
	if( !$con )
		die( 'Could not connect: ' . mysql_error() );
		
	mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
	$query_str = "SELECT d_ip,d_port,l_ip,l_port FROM dev_db.dev_table WHERE guid1='".$_POST['g1']."'";
	$res = mysql_query( $query_str, $con );
	if( empty($res) ) {
		mysql_close( $con );
		return;
	}
	
	while( $row=mysql_fetch_array($res) ) {
		$r_ip = $row[0];
		$r_port = intval( $row[1] );
		$l_ip = $row[2];
		$l_port = intval( $row[3] );
	}
	mysql_close( $con );
	
	if( empty($r_ip) | empty($l_ip) | $r_port<=0 | $l_port<=0 )
		return;
	
//-----------------------------------------------------------------------------------------------
	$sock = socket_create( AF_INET, SOCK_STREAM, 0 );
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>4, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	
	try {
		socket_bind( $sock, $l_ip, $l_port );       		// 绑定客户端连接时，服务器的 ip、port
		socket_connect( $sock, $r_ip, $r_port );
	} catch (Exception $e) {
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "NO";
		exit;
	}
	//echo "send message!\n";
	//$buff = "wangdehi\r\n";
	sleep( 1 );						//matlab程序时，需要设置等待
	
	$buff = $_POST['cmd'];
	$res = socket_write( $sock, $buff, strlen($buff) );
	if( $res===FALSE )
		echo "NO";
	else
		echo "OK";
			
	socket_close( $sock );

?>