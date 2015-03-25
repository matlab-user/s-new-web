<?php

	// $T -  时间信息解码
	Function decode_time( $T_str ) {
		
		$i = 0;
		$token = strtok( $T_str, ", \n\r" );
		while( $token!==false ) {
			$t_array[$i] = $token;
			$i++;
			$token = strtok( ", \n\r" );
		}
		
		$mid = 0;
		$i = 0;
		foreach( $t_array as $v ) {
		
			if( $i==0 ) {
				$T_int[0] = intval( $v );
				$mid = $T_int[0];
			}
			else {
				$fc = substr( $t_array[$i], 0, 1 );
				switch( $fc ) {
					case '0':
					case '1':
					case '2':
					case '3':
					case '4':
					case '5':
					case '6':
					case '7':
					case '8':
					case '9':
					case '-':
						$T_int[$i] = $mid + intval( $t_array[$i] );
						break;
					default:
						$mid = intval( $t_array[$i] );
						$T_int[$i] = $mid;
						break;
				}
			}
			$i++;
		}
		
		unset( $t_array );

		return $T_int;
	}
	
	//<<<<<<---------------------------->>>>>>
	class W_obj {
		public $dev_id = '';
		public $data = array();
		public $base_t = 0;
	}
	
	class data_obj {
		public $id = '';
		public $value;
		public $time = 0;
		public $unit = '';
		public $type = '';
	}
	
	// 解码 W 数据
	Function decode_W( $W ) {
	
		$i = 0;
		$res = false;
		
		$token = strtok( $W, "[] \n\r" );
		while( $token!==false ) {
			$t_array[$i] = $token;
			$i++;
			$token = strtok( "[] \n\r" );
		}
		
		$i = 0;
		foreach( $t_array as $v ) {
			$token = strtok( $v, "; " );
			if( $token!==false ) {
				$res[$i] = new W_obj;
				$res[$i]->dev_id = $token;
				
				$j = 0;
				$d = strtok( "; " );		// $d 中此时为 (xxxx)(xxxxx)
				$token = strtok( $d, "()" );
				while( $token!==false ) {
					$d_array[$j] = $token;
					$j++;
					$token = strtok( "()" );
				}
				
				foreach( $d_array as $dv ) {
					$res_d = decode_data( $dv );
					if( empty($res_d)==false )
						array_push( $res[$i]->data, $res_d );
					$token = strtok( "()" );
				}
				$i++;
				//unset( $d_array );
				$d_array = [];
			}
		}
		
		return $res;
	}

	// $d - 参数数据, 无"()", xxxx
	Function decode_data( $d ) {
	
		$res_d = new data_obj;
		
		$token = strtok( $d, "," );
		if( $token!==false )
			$res_d->id = $token;
		else 
			return '';
		
		$token = strtok( "," );
		if( $token!==false )
			$mid_value = $token ;
		else 
			return '';
		
		$token = strtok( "," );
		while( $token!==false ) {
			switch( substr($token,0,1) ) {
				case 't':
					$res_d->time = floatval( substr($token,1) );
					break;
				case 'u':
					$res_d->unit = substr( $token,1 );
					break;
				case 'd':
					$res_d->type = substr( $token, 1 );		
					break;
				default:
					break;
			}
			$token = strtok( "," );
		}
		
		if( empty( $res_d->type ) )
			$res_d->type = 'value';
			
		switch( $res_d->type ) {
			case 'value':
				$res_d->value = floatval( $mid_value );
				break;
			default:
				$res_d->value = $mid_value;
				break;
		}
		
		return $res_d;
	}
	
	// count data's time 
	// $T - 数组，存储每台设备的基准时间，皆为 完整时间戳 数据
	// $Wobjs - 由 decode_W() 返回的数据, 为 class W_obj 数组
	// 如果 $T 和 $Wobjs 不是一一对应的，返回 false
	Function count_data_time( $T, &$Wobjs ) {
		
		$t_num = count( $T );
		$w_num = count( $Wobjs ) ;

		if( $t_num!=$w_num )
			return false;
		
		$i = 0;
		foreach( $T as $v ) {
			$Wobjs[$i]->base_t = $v;
			foreach( $Wobjs[$i]->data as &$value ) {
				$value->time += $v; 
			}
			$i++;
		}
	}

	// show all contents of W_obj
	// $Wobj 为 class W_obj 数组
	Function show_W_obj( $Wobjs ) {

		foreach( $Wobjs as $v ) {
			echo "dev_id: ".$v->dev_id."\tbase_t: ".$v->base_t."\n";
			foreach( $v->data as $dv ) {
				echo "\tp_id: ".$dv->id."\n";
				echo "\tp_value: ".$dv->value."\n";
				echo "\tp_type: ".$dv->type."\n";
				echo "\tp_time: ".$dv->time."\n";
				echo "\n";
			}
			echo "\n";
		}
	}
	
	class key_obj {
		public $f = '';
		public $t = '';
		public $time = '';
		public $l = '';
		public $s = '';
		public $a = '';
		public $e = '';
		public $c = '';
		public $v = '';
		public $i1 = '';
		public $i2 = '';
		
		Function show() {
			foreach( $this as $key=>$v ) {
				if( empty($v)==false )
					echo $key.' = '.$v."\n";
			}
		}
	}
	
	// 解码自定义的文件传输数据，$file_d 为字符串类型
	// 仅解码数据中 key-value 对部分
	Function decode_file_data( $file_d ) {
	
		$res = new key_obj;
		$pos = strpos( $file_d, "\r" );
		
		if( $pos!==false ) {
		
			$str1 = substr( $file_d, 0, $pos );
			$i = 0;
			$token = strtok( $str1, "\0 \r\n" );
			
			while( $token!==false ) {
				$keys[$i] = $token;
				$i++;
				$token = strtok( "\0 \r\n" );
			}
			
			foreach( $keys as $v ) {
				$t1 = strtok( $v, "= " );
				$t2 = strtok( "= " );
				switch( $t1 ) {
					case 'A':
						$res->a = $t2;
						break;
					case 'V':
						$res->v = $t2;
						break;
					case 'E':
						$res->e = $t2;
						break;
					case 'C':
						$res->c = $t2;
						break;
					case 'I1':
						$res->i1 = $t2;
						break;
					case 'I2':
						$res->i2 = $t2;
						break;
					case 'F':
						$res->f = $t2;
						break;
					case 'T':
						$res->t = $t2;
						break;
					case 'L':
						$res->l = $t2;
						break;
					case 'S':
						$res->s = $t2;
						break;
					case 'TIME':
						$res->time = $t2;
						break;
					default:
						break;
				}
			}
		}
		
		return $res;
	}
	
	// 读取文件真实数据
	Function get_file_data( $file_d ) {
		$res = '';
		$pos = strpos( $file_d, "\r" );
		$res = substr( $file_d, $pos );
		return $res;
	}
	
	class config {
		public $user = '';
		public $pass = '';
		public $upload_path = '';
		public $domain = '';
	}
	
	// 从文件中读取 mysql 数据库用户、密钥等配置信息
	Function read_config( $file_name ) {
	
		$fid = fopen( $file_name, 'r' ); 
		if( $fid ) { 
			$res = new config;
			while ( !feof($fid) ) {
				$buffer = fgets( $fid, 1024 );
				$token = strtok( $buffer, "= \r\n" );
				switch( $token ) {
					case 'mysql_user':
						$res->user = strtok( "= \r\n" );
						break;
					case 'mysql_passwd':
						$res->pass = strtok( "= \r\n" );
						break;
					case 'upload_path':
						$res->upload_path = strtok( "= \r\n" );
						break;
					case 'domain':
						$res->domain = strtok( "= \r\n" );
						break;
					default:
						break;
				}
			}
		} 
		else
			return false;
			
		fclose( $fid );
		return $res;
	}
	
	// 依次建立 $dirs 中给定的各级文件夹
	Function mkdirs( $dirs ) {
		$path = '/';
		$token = strtok( $dirs, "/" );
		while( $token!==false ) {
			$path .= $token.'/';
			//echo $path."\n";
			if( !is_dir($path) )
				mkdir( $path );
			$token = strtok( "/" );
		}
	}
?>