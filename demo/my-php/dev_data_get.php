<?php
	
	session_start();
/*	
	$_POST['g1'] ='demo-1';
	$_POST['d_id'] = 4;
	$_POST['lt'] = time()-10;
	$_POST['tz'] = 8;
*/	

	require_once( './php-lib/codec_lib.php' );
	
	$config = read_config( './php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	
	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
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

	while( $row=mysql_fetch_array($res) ) {
		
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
				$img[0] = 'http://img.camarts.cn/2015/03/IMG_0945.jpg?imageView/2/w/800/h/800/q/90';
				$img[1] = 'http://img.camarts.cn/2015/03/IMG_0920.jpg?imageView/2/w/800/h/800/q/90';
				$img[2] = 'http://img.camarts.cn/2015/03/IMG_0895.jpg?imageView/2/w/800/h/800/q/90';
				$img[3] = 'http://img.camarts.cn/2015/02/IMG_0870.jpg?imageView/2/w/800/h/800/q/90';
				$img[4] = 'http://img.camarts.cn/2015/02/IMG_0832.jpg?imageView/2/w/800/h/800/q/90';
				$img[5] = 'http://img.camarts.cn/2015/02/IMG_0791.jpg?imageView/2/w/800/h/800/q/90';
				$img[6] = 'http://img.camarts.cn/2015/02/IMG_0771.jpg?imageView/2/w/800/h/800/q/90';
				$img[7] = 'http://img.camarts.cn/2015/01/IMG_0749.jpg?imageView/2/w/800/h/800/q/90';
				$img[8] = 'http://img.camarts.cn/2015/01/IMG_0730.jpg?imageView/2/w/800/h/800/q/90';
				
				$t_s = get_today_start( $_POST['tz'] );
				$step = 30;	
				$mid1 = floor( (time()-$t_s)/$step ) * $step;
				$in_t = $t - $t_s;
				if( $in_t<0 ) 
					$in_t = 0;
				
				if( $mid1<$in_t )
					return '';
				
				$_SESSION['img_lt'] = $mid1 + $t_s;
				if( $_SESSION['img_lt']==$t )
					return '';
				
				if( !isset($_SESSION['cur_img']) ) {
					$_SESSION['cur_img'] = 0;
					$x_str = "<d id='".$d_id."'>";
					$res = addslashes( $img[$_SESSION['cur_img']] );
					$x_str .= "<v t='".($mid1+$t_s)."'>".$res."</v></d>";
					break;
				}
				
				$_SESSION['cur_img']++;
				if( $_SESSION['cur_img']>=9 )
					$_SESSION['cur_img'] = 0;
				
				$x_str = "<d id='".$d_id."'>";
				$res = addslashes( $img[$_SESSION['cur_img']] );
				$x_str .= "<v t='".($mid1+$t_s)."'>".$res."</v></d>";
				break;
				
			case 'state':
				$x_str = "<d id='".$d_id."'><v t='".time()."'>".rand(0,pow(2,16)-1)."</v></d>";
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
	$step = 20;				// 间隔
	
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
	
	$step = 20;				// 间隔
	
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