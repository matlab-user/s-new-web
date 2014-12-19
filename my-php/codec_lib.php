<?php

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

?>