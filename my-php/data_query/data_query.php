<?php

	require_once( "../php-lib/codec_lib.php" );

	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
/*
	$_POST['g1'] = '12091200160055';
	$_POST['t0'] = 0;
	$_POST['t1'] = 1430888983;
	$_POST['d_id'] = 1;
*/
	if( !(isset($_POST['t0']) && isset($_POST['d_id']) && isset($_POST['g1']) && isset($_POST['t1'])) )
		exit;

	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );
	mysql_query("SET NAMES 'utf8'", $con);
	$xml .= get_d_range( $_POST['g1'], $_POST['d_id'], $_POST['t0'], $_POST['t1'], $con );
	mysql_close( $con );
	
	$xml .= '</xml>';
	echo $xml."\r\n";
	
//---------------------------------------------------------------------------------------
	// 读取 dev_id 设备的参数信息
	// $t_s 设备所在地当天0点的 UTC 时间
	// 当 $t==0， 读取 [$t_s $now] 间的数据（仅对累积型数据有效）
	// 当 $t!=0， 读取 ($lt $now] 间的数据 （仅对累积型数据有效）
	// $con 数据库句柄
	function get_d_range( $dev_id, $d_id, $t0, $t1, $con ) {
			
		$x_str = '';
		$res = mysql_query( "SELECT utid, v_name, d_t, remark FROM data_db.dev_data_unit WHERE dev_id='".$dev_id."' AND d_id=".$d_id, $con );
		if( empty($res) )
			return '';

		while( $row=mysql_fetch_array($res) ) {
			
			$ty = $row[1];
			$utid = $row[0];
			$d_t = $row[2];
			$remark = $row[3];
						
			// 获取单位
			$unit = '';
			$res1 = mysql_query( "SELECT unit FROM data_db.unit_table WHERE id=".$utid, $con );
			while( $row1 = mysql_fetch_array( $res1 ) )
				$unit = $row1[0];
			mysql_free_result( $res1 );
						
			if( strpos($unit,'file')===FALSE ) {
				// 获取值
				switch( $d_t ) {
					case 0:
				
						$sql_str = "SELECT value, time FROM data_db.his_data WHERE dev_id='$dev_id' AND d_id=$d_id AND time>=$t0 AND time<=$t1 ORDER BY time ASC";

						$res3 = mysql_query( $sql_str, $con );	
						$i = 0;					
						while( $row3=mysql_fetch_array($res3) ) {
							if( $i==0 ) {
								$x_str .= "<d id='".$d_id."'>";
								$base_t = floatval( $row3[1] );
								$x_str .= '<base_t>'.$base_t.'</base_t>';
								$i++;
							}
							
							$dt = floatval($row3[1]) - $base_t;
							$x_str .= "<v t='".$dt."'>".$row3[0]."</v>";
						}
						if( $i>0 )
							$x_str .= "</d>";
						mysql_free_result( $res3 );
						break;
							
					default:
						break;
				}
			}	

		}
		
		return $x_str;
	}
?>