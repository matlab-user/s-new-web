<?php
// 验证用户输入的设备 guid1、rcode 信息是否正确
// 输入正确，且设备成功 insert 进 dev_db.dev_table 中，返回 OK；
// 否则不输出任何信息

	session_start();
/*	
	$_SESSION['user'] = 'free-bug@163.com';
	$_POST['g1'] = 'wdh';
	$_POST['rcode'] = 'wangdehui';
*/	
	if( !isset($_SESSION['user'])  )
		exit;
		
	if( !(isset($_POST['g1']) && isset($_POST['rcode'])) )
		exit;
	
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
	$con = touch_mysql();
	if( empty($con) )
		exit;
	
	$_POST['g1'] = mysql_real_escape_string( $_POST['g1'] );
	$_POST['rcode'] = mysql_real_escape_string( $_POST['rcode'] );
	
	$sql_str = "SELECT guid2, name, model, maker, rcode, logo FROM dev_db.dev_store WHERE guid1='".$_POST['g1']."'";

	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return;
	
	$row = mysql_fetch_array( $res );
	$g2 = $row[0];
	$name = $row[1];
	$model = $row[2];
	$maker = $row[3];
	$rcode = $row[4];
	$logo = $row[5];
		
	mysql_free_result ( $res );
	
	if( $_POST['rcode']===$rcode ) {
		$sql_str = "INSERT INTO dev_db.dev_table ( guid1, guid2, name, model, maker, state, owner, logo ) VALUES ('".$_POST['g1']."', '$g2', '$name', '$model', '$maker', 'running', '".$_SESSION['user']."', '$logo')";
		mysql_unbuffered_query( $sql_str, $con );
		
		$sql_str = "SELECT ROW_COUNT()";
		$res = mysql_query( $sql_str, $con );
		if( !empty($res) ) {
			$row = mysql_fetch_array( $res );
			if( $row[0]>0 )
				echo $_POST['g1'].",$name";
		}
		
		$sql_str = "UPDATE dev_db.dev_store SET state='active' WHERE guid1='".$_POST['g1']."'";
		mysql_unbuffered_query( $sql_str, $con );	
	}
	
	mysql_close( $con );
	
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