<?php
	session_start();
/*
	if( !isset($_SESSION['user'])  )
		exit;
*/	
	$_POST['dev_num'] = 3000;
	
	if( $_POST['dev_num']>3000 )
		$_POST['dev_num'] = 3000;
	
	$_POST['name'] = 'wang';
	$_POST['model'] = 'w1';
	$_POST['maker'] = 'wangdehui';
	$_POST['m_code'] = 'xx';
	
	
/*	
	if( !(isset($_POST['g1']) && isset($_POST['rcode'])) )
		exit;
*/
	require_once( "../php-lib/randcode.php" );
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;	
	
	$name = $_POST['name'];
	$model = $_POST['model'];
	$maker = $_POST['maker'];
	$mcode = $_POST['m_code'];

	$con = touch_mysql();
	if( empty($con) )
		exit;
	
	$has_add = add_devs_into_dev_store( $_POST['dev_num'], $con );
			
	mysql_close( $con );

	echo "$has_add\r\n";
	
//---------------------------------------------------------------------------------------
	function touch_mysql() {
		global $mysql_user, $mysql_pass;
		$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
		if( !$con )
			die( 'Could not connect: ' . mysql_error() );	
		mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
		mysql_select_db( 'dev_db', $con );
		return $con;
	}
	
	// $dev_num - 需要添加的设备数量
	// 返回成功添加的设备数量
	function add_devs_into_dev_store( $dev_num, $con) {
		global $name, $model, $maker, $mcode;
		
		$has_add = 0;
		
		$i = 0;
		$sql_str = "INSERT INTO dev_store (guid1, name, model, maker, m_code, rcode) VALUE ";
		for( $i=0; $i<$dev_num; $i++ ) {
			$g1 = $mcode.randCode( 7, 0 );
			$rcode = randCode( 16, 0 );
			if( $i==$dev_num-1 )
				$sql_str .= "('$g1','$name','$model','$maker','$mcode','$rcode')";
			else 
				$sql_str .= "('$g1','$name','$model','$maker','$mcode','$rcode'),";
		}
		
		mysql_unbuffered_query( $sql_str, $con );
					
		$sql_str = "SELECT ROW_COUNT()";
		$res = mysql_query( $sql_str, $con );
		if( !empty($res) ) {
			$row = mysql_fetch_array( $res );
			$has_add = $row[0]+0;
		}
		
		for( $j=0; $j<10; $j++ ) {
			
			$rest_dev = $dev_num - $has_add;
			if( $rest_dev<=0 )
				break;
					
			$sql_str = '';
			for( $i=0; $i<$rest_dev; $i++ ) {
				$g1 = $mcode.randCode( 7, 0 );
				$rcode = randCode( 16, 0 );
				$sql_str = "INSERT INTO dev_store (guid1, name, model, maker, m_code, rcode) VALUE ('$g1','$name','$model','$maker','$mcode','$rcode')";	
				
				mysql_unbuffered_query( $sql_str, $con );
					
				$sql_str = "SELECT ROW_COUNT()";
				$res = mysql_query( $sql_str, $con );
				if( !empty($res) ) {
					$row = mysql_fetch_array( $res );
					if( $row[0]>0 )
						$has_add += 1;
					else
						break;
				}
			}

		}
		
		return $has_add;
		
	}
?>