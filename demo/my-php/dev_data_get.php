<?php
	//$_POST['g1'] ='demo-1';
	//$_POST['d_id'] = 1;
	//$_POST['lt'] = 0;
	//$_POST['tz'] = 8;
	
	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", "root", "blue" );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	
	$xml .= get_d( $_POST['g1'], $_POST['d_id'], $_POST['lt'], $con );
	
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
function get_d( $dev_id, $d_id, $t, $con ) {
		
	$x_str = '';
	$res = mysql_query( "SELECT utid, v_name, d_t, remark FROM data_db.dev_data_unit WHERE dev_id='".$dev_id."' AND d_id=".$d_id, $con );
	if( empty($res) )
		return '';

	while( $row = mysql_fetch_array( $res ) ) {
		
		$utid = $row[0];
		$d_t = $row[2];

		// 获取单位
		$unit = '';
		$res1 = mysql_query( "SELECT unit FROM data_db.unit_table WHERE id=".$utid, $con );
		while( $row1 = mysql_fetch_array( $res1 ) )
			$unit = $row1[0];
		mysql_free_result( $res1 );
		
		switch( $unit ) {
			case 'file/image':
				break;
			
			default:
				if( $d_t=='0' )
					$x_str = get_ds( $t, $d_id );
				
				if( $d_t=='1' )
					$x_str = get_one_d( $t, $d_id );
				break;
		}
	}			
	return $x_str;
}

// $in_t - 输入的 UTC 时间, 单位：秒
function get_one_d( $in_t, $d_id ) {
	
	$now = time();
	$step = 10;				// 间隔10秒
	
	$u = 13.5 * 3600;	
	$t_s = get_today_start( $_POST['tz'] );
	
	$in_t -= $t_s;
    if( $in_t<0 ) 
		$in_t = 0;

	$p1 = 100 / sqrt( 2*pi() );
	
	$mid1 = floor( ($now-$t_s)/$step ) * $step;
	if( $mid1<$in_t )
		return '';

	$res = $p1 * exp( -1*pow($mid1-$u,2)/900000000 );
	$res = number_format( $res, 2 );
	
	$xstr = "<d id='".$d_id."'>";
	$xstr .= "<v t='".($mid1+$t_s)."'>".$res."</v></d>";
	
    return $xstr;
}

function get_ds( $in_t, $d_id ) {
	
	$step = 10800;				// 间隔10秒
	
	$u = 13.5 * 3600;	
	$t_s = get_today_start( $_POST['tz'] );
	//$now = $t_s + 10;
	$now = time();
		
	$in_t -= $t_s;
    if( $in_t<0 ) 
		$in_t = 0;

	$p1 = 100 / sqrt( 2*pi() );
	
	$t2 = floor( ($now-$t_s)/$step );
	$mid1 =  $t2 * $step;
	if( $mid1<$in_t )
		return '';
	
	$t1 = ceil( $in_t/$step );
	
	$xstr = "<d id='".$d_id."'>";
	for( $i=$t1; $i<=$t2; $i++ ) {
		$mid1 = $step * $i;
		if( $i==$t1 ) {
			$base_t = $t_s + $mid1;
			$xstr .= '<base_t>'.$base_t.'</base_t>';
		}
		
		$res = $p1 * exp( -1*pow($mid1-$u,2)/900000000 );
		$res = number_format( $res, 2 );
		$xstr .= "<v t='".($mid1+$t_s-$base_t)."'>".$res."</v>";
	}
	$xstr .= "</d>"; 
	
    return $xstr;
}
?>