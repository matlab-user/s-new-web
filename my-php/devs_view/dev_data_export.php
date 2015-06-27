<?php

	require_once( './PHPExcel_1.8.0/Classes/PHPExcel.php' );
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;

	$excel_col = array();
	for( $i=0; $i<26; $i++ )
		$excel_col[] = chr( ord('A')+$i );
	for( $j=0; $j<4; $j++ )
		for( $i=0; $i<26; $i++)
			$excel_col[] = $excel_col[$j].$excel_col[$i];
		
/*	
	$_POST['devs'] = array('EXGnG9vV','hO4Mx9Yh','rZteCbX2','TFwqovfw');
	$_POST['st'] = 0;
	$_POST['et'] = time();
	$_POST['save_name'] = 'wdh.xlsx';
*/	
	if( !isset($_POST['devs']) | !isset($_POST['st']) | !isset($_POST['et']) | !isset($_POST['save_name'])  )
		exit;
	
	$_POST['devs'] = split ( ',', $_POST['devs'] );
		
	// 前端传来 1970-1-2 14：0：0，表示是设备所在地区的时间；
	// 为了处理方便，先把此时间当成0时区时间，获得时间戳 ST；
	// 则当设备所在地到此时间，对应的UTC时间为 ST-dev_TZ×3600
	date_default_timezone_set( 'UTC' );
	$st = strtotime( $_POST['st'] );
	$et = strtotime( $_POST['et'] );
		
	$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );
	mysql_query("SET NAMES 'utf8'", $con);
	
	foreach( $_POST['devs'] as &$v )
		$v = mysql_real_escape_string( $v );
	unset( $v );
	
	$devs_info = array();
	
// 获取设备信息
	$sql_str = "SELECT guid1, name, model, maker, timezone FROM dev_db.dev_table WHERE";
	foreach( $_POST['devs'] as $v )
		$sql_str .= " guid1='$v' OR";
	$sql_str = rtrim( $sql_str, "R" );
	$sql_str = rtrim( $sql_str, "O" );
	$sql_str = rtrim( $sql_str );

	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return '';
	
	$sheet_id = 0;
	while( $row = mysql_fetch_array($res) ) {
		$t = $devs_info[$row[0]] = new dev_info;		
		$t->name = str_replace( array("\\",'/'), '', $row[1] );
		$t->model = $row[2];
		$t->maker = $row[3];
		$t->tz = intval( $row[4] );
		$t->sheet_id = $sheet_id;
		$sheet_id++;
	}
	mysql_free_result ( $res );
	
// 获取设备上传数据信息
	$sql_str = "SELECT dev_id, d_id, utid, v_name FROM data_db.dev_data_unit WHERE d_t=0 AND ";
	foreach( $_POST['devs'] as $v )
		$sql_str .= " dev_id='$v' OR";
	$sql_str = rtrim( $sql_str, "R" );
	$sql_str = rtrim( $sql_str, "O" );
	$sql_str .= "ORDER BY d_id ASC";
	
	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return '';
	
	$utid_array = array();
	while( $row = mysql_fetch_array($res) ) {
		$t = $devs_info[$row[0]]->ds[$row[1]] = new data_info;
		$utid_array[] = $t->unit_id = $row[2];
		$t->name = $row[3];
	}
	mysql_free_result ( $res );
	
	$utid_array = array_unique( $utid_array );
	unset( $_POST['devs'] );
	
// 获取 utid 单位名称
	$sql_str = "SELECT id, unit FROM data_db.unit_table WHERE ";
	foreach( $utid_array as $v )
		$sql_str .= " id='$v' OR";
	$sql_str = rtrim( $sql_str, "R" );
	$sql_str = rtrim( $sql_str, "O" );
	$sql_str = rtrim( $sql_str );
	
	$res = mysql_query( $sql_str, $con );
	if( empty($res) )
		return '';
	
	$unit = array();
	
	while( $row = mysql_fetch_array($res) )
		$unit[$row[0]] = $row[1];					// $unit[utid]
	mysql_free_result ( $res );	
	
	//var_dump( $devs_info );
	//var_dump( $utid_array );
	//var_dump( $unit );
	unset( $utid_array );

// 生成 excel 文件		
	$PHPExcel = new PHPExcel();
	$PHPExcel->getProperties()->setCreator( 'swaylink' );
	$PHPExcel->getProperties()->setLastModifiedBy( 'swaylink_dev_data_export' );
	$PHPExcel->getProperties()->setTitle( 'devs_data' );
	
	$loop = 0;
	foreach( $devs_info as $k => $v ) {
		if( $loop>0 )
			$PHPExcel->createSheet(); 
		
		$loop++;
		
		$PHPExcel->setActiveSheetIndex( $v->sheet_id );	
		
		$PHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth( 30 );
		$PHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth( 20 );
		$PHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth( 30 );
		$PHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth( 30 );
		$PHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth( 30 );
				
		$PHPExcel->getActiveSheet()->setTitle( $v->name );
		$PHPExcel->getActiveSheet()->setCellValue( 'A1', "设备名称：$v->name" );
		$PHPExcel->getActiveSheet()->setCellValue( 'B1', "GUID：$k" );
		$PHPExcel->getActiveSheet()->setCellValue( 'C1', "型号：$v->model" );
		$PHPExcel->getActiveSheet()->setCellValue( 'D1', "制造商：$v->maker" );
		
		if( $v->tz>0 )
			$tz_str = "东$v->tz"."区";
		else
			$tz_str = "西".abs($v->tz)."区";
			
		$PHPExcel->getActiveSheet()->setCellValue( 'E1', "所在时区：$tz_str" );
		
		$j = 0;
		foreach( $v->ds as $k2 => $v2 ) {
			
			$unit_name = $unit[$v2->unit_id];
			if( $unit_name==='bin' | $unit_name==='utf8' | strpos($unit_name,"file/")!==FALSE )
				continue;
				
			$col1 = $excel_col[4*$j];
			$col2 = $excel_col[4*$j+1];
			$col3 = $excel_col[4*$j+2];
			
			$PHPExcel->getActiveSheet()->getColumnDimension( $col1 )->setWidth( 30 );
			$PHPExcel->getActiveSheet()->getColumnDimension( $col2 )->setWidth( 20 );
			$PHPExcel->getActiveSheet()->getColumnDimension( $col3 )->setWidth( 30 );
			
			$PHPExcel->getActiveSheet()->getStyle($col1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$PHPExcel->getActiveSheet()->getStyle($col2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$PHPExcel->getActiveSheet()->getStyle($col3)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$PHPExcel->getActiveSheet()->getStyle($col1.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$PHPExcel->getActiveSheet()->getStyle($col2.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$PHPExcel->getActiveSheet()->getStyle($col3.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$PHPExcel->getActiveSheet()->getStyle($col1.'4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$PHPExcel->getActiveSheet()->getStyle($col2.'4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$PHPExcel->getActiveSheet()->getStyle($col3.'4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$PHPExcel->getActiveSheet()->setCellValue( $col1.'3', "数据名称：$v2->name" );
			$PHPExcel->getActiveSheet()->setCellValue( $col2.'3', "数据id：$k2" );
			$PHPExcel->getActiveSheet()->setCellValue( $col3.'3', "单位：$unit_name" );
			
			$PHPExcel->getActiveSheet()->setCellValue( $col1.'4', "值" );
			$PHPExcel->getActiveSheet()->setCellValue( $col2.'4', "UTC时间" );
			$PHPExcel->getActiveSheet()->setCellValue( $col3.'4', "本地时间" );
			
			// 获取数据
			$dev_st = $st - $v->tz*3600;
			$dev_et = $et - $v->tz*3600;
			$sql_str = "SELECT value, time FROM data_db.his_data WHERE d_id=$k2 AND dev_id='$k' AND time>=$dev_st AND time<=$dev_et ORDER BY time ASC LIMIT 10";
			$res = mysql_query( $sql_str, $con );
			if( empty($res) )
				return '';
			$r_num = 5;
			while( $row = mysql_fetch_array($res) ) {
				$PHPExcel->getActiveSheet()->setCellValue( $col1."$r_num", floatval($row[0]) );
				$PHPExcel->getActiveSheet()->setCellValue( $col2."$r_num", floatval($row[1]) );
				$locale_t = gmdate( 'Y-m-d H:i:s', floatval($row[1])+($v->tz*3600) );
				$PHPExcel->getActiveSheet()->setCellValue( $col3."$r_num", $locale_t );
				$r_num++;
			}
			
			mysql_free_result ( $res );	
			$j++;
		}
	}
	
	mysql_close( $con );
	
	$excel_writer = new PHPExcel_Writer_Excel2007( $PHPExcel );
	//$excel_writer->save( 'wdh.xlsx' );
	
	header( 'Pragma: public' );
	header( 'Expires: 0' );
	header( 'Cache-Control:must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Type:application/force-download' );
	header( 'Content-Type:application/vnd.ms-execl' );
	header( 'Content-Type:application/octet-stream');
	header( 'Content-Type:application/download' );
	header( 'Content-Disposition:attachment;filename="'.$_POST['save_name'].'"' );
	header( 'Content-Transfer-Encoding:binary' );
	$excel_writer->save( 'php://output' );
	
//=============================================================
	class dev_info {						// dev_info[guid1]
		public $name = '';
		public $model = '';
		public $maker = '';
		public $tz = 8;
		public $sheet_id = 0;				// 生成 excel 时使用
		public $ds = array();				// $ds[d_id]
	}
	
	class data_info {
		public $name = '';
		public $unit_id = ''; 
	}
?>
