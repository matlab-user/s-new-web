 <?php
	
	//$_POST['g1'] = '1982011602030410182910a1F2C3D02A';
	
	if( !isset($_POST['g1']) )
		exit;
	
	$xml = '<xml>';
	
	$con = mysql_connect( "localhost", "root", "blue" );
	if ( !$con )
		die( 'Could not connect: ' . mysql_error() );

	mysql_query("SET NAMES 'utf8'", $con);
	mysql_select_db( "dev_db", $con );
	
	$res = mysql_query( "SELECT fun_id, fun_name, remark, p_num, p1, p2, p3, p4, p1_name, p2_name, p3_name, p4_name, p1_remark, p2_remark, p3_remark, p4_remark FROM dev_fun WHERE m<>'a' AND dev_id='".$_POST['g1']."'", $con );
	if( empty($res) )
		return '';
/*
<op id=‘**’>
	<n>操作名称</n>
	<rm>***</rm>
	<p ind=’1’>
		<pn>参数名称</pn>
		<prm>参数说明</prm>
		<pu>参数单位</pu>（可选参数）
	</p>
	<p ind=‘2’>
		<pn>参数名称</pn>
		<prm>参数说明</prm>
		<pu>参数单位</pu>
	</p>
</op>
*/
	while( $row=mysql_fetch_array($res) ) {
		$p_num = $row[3];
		$xml .= "<op id='".$row[0]."'>";
		$xml .= "<n>".$row[1]."</n>";
		$xml .= "<rm>".$row[2]."</rm>";
		
		$p = array();	// 记录参数单位索引号
		$p_name = array();
		$p_remark = array();
		$sql_str = '';
		$p_unit = array();
		$p_unit_id = array();
		
		switch($p_num) {
			case 1:
				array_push( $p, $row[4] );
				$sql_str = 'id='.$p[0];
				
				array_push( $p_name, $row[8] );
				
				array_push( $p_remark, $row[12] );
				break;
			case 2:
				array_push( $p, $row[4] );
				array_push( $p, $row[5] );
				$sql_str = 'id='.$p[0].' OR id='.$p[1];
				
				array_push( $p_name, $row[8] );
				array_push( $p_name, $row[9] );
				
				array_push( $p_remark, $row[12] );
				array_push( $p_remark, $row[13] );	
				break;
			case 3:
				array_push( $p, $row[4] );
				array_push( $p, $row[5] );
				array_push( $p, $row[6] );
				$sql_str = 'id='.$p[0].' OR id='.$p[1].' OR id='.$p[2];
				
				array_push( $p_name, $row[8] );
				array_push( $p_name, $row[9] );
				array_push( $p_name, $row[10] );
				
				array_push( $p_remark, $row[12] );
				array_push( $p_remark, $row[13] );
				array_push( $p_remark, $row[14] );
				break;
			case 4:
				array_push( $p, $row[4] );
				array_push( $p, $row[5] );
				array_push( $p, $row[6] );
				array_push( $p, $row[7] );
				$sql_str = 'id='.$p[0].' OR id='.$p[1].' OR id='.$p[2].' OR id='.$p[3];
				
				array_push( $p_name, $row[8] );
				array_push( $p_name, $row[9] );
				array_push( $p_name, $row[10] );
				array_push( $p_name, $row[11] );
				
				array_push( $p_remark, $row[12] );
				array_push( $p_remark, $row[13] );
				array_push( $p_remark, $row[14] );
				array_push( $p_remark, $row[15] );
				break;
			default:
				break;
		}
		
		if( !empty($sql_str) ) {
			$res2 = mysql_query( "SELECT id, unit FROM data_db.unit_table WHERE ".$sql_str, $con );
			if( !empty($res2) ) {	
				while( $row2=mysql_fetch_array($res2) ) {
					array_push( $p_unit_id, $row2[0] );
					array_push( $p_unit, $row2[1] );
					//var_dump( $p_unit_id );
				}
				mysql_free_result ( $res2 );
			}
		}
		
		foreach( $p as $k=>$v ) {
			$mid_unit = '';
			foreach( $p_unit_id as $k2=>$v2 ) { 
				//echo $v."-------".$v2."\r\n";
				if( $v2==$v ) {
					$mid_unit = $p_unit[$k2];
					break;
				}
			}

			$xml .= "<p ind=".($k+1).">";
			$xml .= "<pn>".$p_name[$k]."</pn>";
			$xml .= "<prm>".$p_remark[$k]."</prm>";
			$xml .= "<pu>".$mid_unit."</pu></p>";
		}
		$xml .= "</op>";
	}
	$xml .= "</xml>";
	mysql_free_result ( $res );
	mysql_close( $con );

	echo $xml."\r\n";
	//echo STDOUT."\r\n";
?>