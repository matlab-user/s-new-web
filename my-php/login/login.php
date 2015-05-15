<?php
	session_start();
	
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;

	if( !( isset($_POST['user']) & isset($_POST['passwd']) ) )
		exit;
	
	if( !isset($_SESSION['l_login']) )
		$_SESSION['l_login'] = time();
	else {
		$now = time();
		if( $now-$_SESSION['l_login']<3 )
			exit;
		else
			$_SESSION['l_login'] = $now;
	}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	$sig = 'NO';
	$sql_con = touch_mysql();
	if( empty($sql_con) )
		exit;
	
	$_POST['user'] = mysql_real_escape_string( $_POST['user'] );
	$query_str = "SELECT passwd,state,uid,dnum,type FROM user_table WHERE mail='".$_POST['user']."'";
	$res = mysql_query( $query_str, $sql_con );
	while( $row=mysql_fetch_array($res) ) {
		if( $row[1]=='inactive' )
			break;

		if( $row[0]==md5($_POST['passwd']) ) {
			
			$_SESSION['user'] = $_POST['user'];
			$_SESSION['dnum'] = $row[3];
			$_SESSION['uid'] = $row[2];
								
			if( $row[4]=='company' ) {
				$sig = 'company_ui.html';
				
				mysql_free_result ( $res );
				$sql_str = "SELECT code, c_name FROM com_code WHERE uid='".$_SESSION['uid']."'";
				$res = mysql_query( $sql_str, $sql_con );
				while( $row=mysql_fetch_array($res) ) {
					$_SESSION['m_code'] = $row[0];
					$_SESSION['m_name'] = $row[1];
				}
				
			}
			else {
				$sig = 'devs_view.html';
			}
		}
	}
	
	mysql_close( $sql_con );
	echo $sig;

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