<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" http-equiv="X-UA-Compatible" content="IE=edge">
		<title>SWAYTECH</title>
		<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
		<style type='text/css'>
			html{-webkit-text-size-adjust:none}
			body{margin:0 auto;color:#333;background:rgb(8,43,81);font-size:14px;font-family:"Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif;line-height:24px;cursor:default}	
			#header{line-height:66px;text-align:center;vertical-align:middle;width:100%;height:66px;background:#000}
			#header img{margin-left:10%;height:100%;float:left}
			.main{width:100%;}
			h2{padding:30px;width:100%;color:#fff;text-align:center;}
			.foot{position:absolute;bottom:0px;vertical-align:middle;margin:0 10%;padding-top:5px;width:80%;height:40px;line-height:12px;font-size:12px;color:#909090;border-top:solid 1px #E0E0E0}
			.foot span{line-height:40px}
			.foot .ICP{float:right}
		</style> 
	</head>
	<body>
		<div id='header'><img src='../../../image/login/swaytech.png'/></div>
	
<?php
	require_once( '../../php-lib/codec_lib.php' );
	$config = read_config( '../../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;$t1 = 1429079466;
$uid = '8tPy7KeJPOolpOIYAQhJDRdn1PW2bHKs';
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

echo "<h2>已经完成激活，请重新登录！</h2>";
echo '<script>setTimeout( "javascript:location.href='."'../../../login.html'".'", 3000 ); </script>';
echo '<div class="foot"><span>&copy;2014 - 2015 Swaytech All Right Reserved.</span><span class="ICP">蜀ICP备14031015号-1</span></div></body></html>';

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