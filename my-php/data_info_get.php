 <?php
	
	//$_POST['g1'] = '1982011602030410182910a1F2C3D02A';
	
	if( !isset($_POST['g1']) )
		exit;
	
	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", "root", "blue" );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	
	$res = mysql_query( "SELECT utid, v_name, d_t, d_id, remark FROM data_db.dev_data_unit WHERE dev_id='".$_POST['g1']."'", $con );
	if( empty($res) )
		return '';
	
	$x_str = '';
	while( $row = mysql_fetch_array( $res ) ) {
		
		$ty = $row[1];
		$utid = $row[0];
		$d_t = $row[2];
		$d_id = $row[3];
		$remark = $row[4];
		
		$x_str .= "<d id='".$d_id."'>";
		$x_str .= "<ty>".$ty."</ty>";
		$x_str .= "<ss>".$d_t."</ss>";
		$x_str .= "<name>".$remark."</name>";
		
		// 获取单位
		$unit = 'W';
		$res1 = mysql_query( "SELECT unit FROM data_db.unit_table WHERE id=".$utid, $con );
		while( $row1 = mysql_fetch_array( $res1 ) )
			$unit = $row1[0];
		mysql_free_result( $res1 );
		$x_str .= "<u>".$unit."</u>";
	}
		
	mysql_free_result ( $res );
	mysql_close( $con );
	
	$xml .= $x_str;
	$xml .= '</xml>';
	echo $xml."\r\n";
?>