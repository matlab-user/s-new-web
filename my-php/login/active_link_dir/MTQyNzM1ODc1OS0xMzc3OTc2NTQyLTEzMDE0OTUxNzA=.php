<!DOCTYPE html>
<html>
    <head><meta charset="UTF-8" http-equiv="X-UA-Compatible" content="IE=edge"></head>
</html>
<?php
	require_once( '../../php-lib/codec_lib.php' );
	$config = read_config( '../../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	$t1 = 1427358759;
$uid = 'NO';
	$now = time();
	if( ($now-$t1)>30*60 ) { // 超时了
		echo "注册超时，请重新注册！";
		unlink(__FILE__);
		return;
	}
		
	$sql_con = touch_mysql();
	if( empty($sql_con) )
		exit;
	$query_str = "UPDATE user_db.user_table SET state='unknown' WHERE uid='".$uid."'";
	mysql_unbuffered_query( $query_str, $sql_con );
	mysql_close( $sql_con );
	
	echo "已经完成激活，请重新登录！";
	echo __FILE__;
	unlink(__FILE__);
	
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