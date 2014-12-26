<?php
	
	//$_POST['tz'] = 8;
	$_POST['g1'] = '1982011602030410182910a1F2C3D02A';
	//$_POST['lt'] = 0;
	//$_POST['d_id'] = 0;
	
	if( !(isset($_POST['tz']) && isset($_POST['d_id']) && isset($_POST['g1']) && isset($_POST['lt'])) )
		exit;
	
	$t_s = get_today_start( $_POST['tz'] );

	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", "root", "blue" );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	
	$xml .= get_d( $_POST['g1'], $_POST['d_id'], $t_s, $_POST['lt'], $con );
	
	mysql_close( $con );
	
	$xml .= '</xml>';
	echo $xml."\r\n";
	
//---------------------------------------------------------------------------------------
	// 计算设备所在地的当天起始UTC时间
	function get_today_start( $tz ) {
		$t = time();
		$t = $t + $tz*3600;
		$year = gmdate("Y", $t);
		$mon = gmdate("m", $t);
		$day = gmdate("d", $t);
		$t = gmmktime(0,0,0,$mon,$day,$year);
		$t -= $tz*3600;
		return $t;
	}
	
	// 读取 dev_id 设备的参数信息
	// $t_s 设备所在地当天0点的 UTC 时间
	// 当 $t==0， 读取 [$t_s $now] 间的数据（仅对累积型数据有效）
	// 当 $t!=0， 读取 ($lt $now] 间的数据 （仅对累积型数据有效）
	// $con 数据库句柄
	function get_d( $dev_id, $d_id, $t_s, $t, $con ) {
			
		$x_str = '';
		$res = mysql_query( "SELECT utid, v_name, d_t, remark FROM data_db.dev_data_unit WHERE dev_id='".$dev_id."' AND d_id=".$d_id, $con );
		if( empty($res) )
			return '';

		while( $row = mysql_fetch_array( $res ) ) {
			
			$ty = $row[1];
			$utid = $row[0];
			$d_t = $row[2];
			$remark = $row[3];
			
			//$x_str .= "<ty>".$ty."</ty>";
			//$x_str .= "<ss>".$d_t."</ss>";
			//$x_str .= "<name>".$remark."</name>";
			
			// 获取单位
			/*
			$unit = 'W';
			$res1 = mysql_query( "SELECT unit FROM data_db.unit_table WHERE id=".$utid, $con );
			while( $row1 = mysql_fetch_array( $res1 ) )
				$unit = $row1[0];
			mysql_free_result( $res1 );
			$x_str .= "<u>".$unit."</u>";
			*/
			
			/*
			// 获取警报相关信息
			$res2 = mysql_query( "SELECT thr, method FROM data_db.alarm WHERE dev_id='".$dev_id."' AND d_id=".$d_id, $con );
			while( $row2 = mysql_fetch_array( $res2 ) )
				$x_str .= "<thr>".$row2[0]."</thr><m>".$row2[1]."</m>";
			mysql_free_result( $res2 );
			*/
			
			// 获取值
			switch( $d_t ) {
				case 0:
					if( $t==0 ) 
						$sql_str = "SELECT value, time FROM data_db.his_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id." AND time>=".$t_s." AND time<=".time()." ORDER BY time ASC";
					else
						$sql_str = "SELECT value, time FROM data_db.his_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id." AND time>".$t." AND time<=".time()." ORDER BY time ASC";
					
					$res3 = mysql_query( $sql_str, $con );	
					$i = 0;					
					while( $row3 = mysql_fetch_array( $res3 ) ) {
						if( $i==0 ) {
							$x_str .= "<d id='".$d_id."'>";
							$base_t = floatval( $row3[1] );
							$x_str .= '<base_t>'.$base_t.'</base_t>';
							$i++;
						}
						
						$dt = floatval($row3[1]) - $base_t;
						$x_str .= "<v t='".$dt."'>".$row3[0]."</v>";
					}

					mysql_free_result( $res3 );
					break;
					
				case 1:
					if( $t==0 ) 
							$sql_str = "SELECT value, time FROM data_db.real_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id;
						else
							$sql_str = "SELECT value, time FROM data_db.real_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id.' AND time>'.$t;
					
					$res3 = mysql_query( $sql_str, $con );
					if( $row3 = mysql_fetch_array( $res3 ) ) {
						$x_str .= "<d id='".$d_id."'>";
						$x_str .= "<v t='".$row3[1]."'>".$row3[0]."</v>";
					}
					mysql_free_result( $res3 );
					break;
					
				case 2:
					if( $t==0 ) 
						$sql_str = "SELECT bin_d, time FROM data_db.real_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id;
					else
						$sql_str = "SELECT bin_d, time FROM data_db.real_data WHERE dev_id='".$dev_id."' AND d_id=".$d_id.' AND time>'.$t;
					
					$res3 = mysql_query( $sql_str, $con );		
					if( $row3 = mysql_fetch_array( $res3 ) ) {
						mysql_query( "UPDATE dev_db.dev_table SET state='need_data' WHERE dev_id='".$dev_id."'", $con );
						if( empty($row3[0]) )
							continue;
						$x_str .= "<d id='".$d_id."'>";
						$x_str .= "<v t='".$row3[1]."'>".$row3[0]."</v>";
					}
					mysql_free_result( $res3 );
					break;
			
				default:
					break;
			}
			
			$x_str .= "</d>";
		}
		
		return $x_str;
	}
?>