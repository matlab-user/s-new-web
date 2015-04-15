<?php
	session_start();
	
	require_once( "../php-lib/codec_lib.php" );
		
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
	$domain = $config->domain;
	
	$frommail = 'swaylink@cdsway.com';
//	$domain = '192.168.31.21';
	
	$_POST['user'] = 'free-bug@163.com';
	$_POST['passwd'] = 'wdh';
	if( !( isset($_POST['user']) & isset($_POST['passwd']) ) )
		exit;
	
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	$sig = 'NO';
	$sql_con = touch_mysql();
	if( empty($sql_con) )
		exit;
	
	$query_str = "SET @UID='';";
	mysql_unbuffered_query( $query_str, $sql_con );
	
	$query_str = "CALL add_user_3( @UID, '".$_POST['user']."', '', '".$_POST['passwd']."', ".time()." )";
	mysql_unbuffered_query( $query_str, $sql_con );
	
	$res = mysql_query( "SELECT @UID;", $sql_con );
	while( $row=mysql_fetch_array($res) ) {
		if( $row[0]=='' )
			$sig = 'NO';
		else
			$sig = $row[0];
	}
	mysql_close( $sql_con );

	if( $sig=='NO' ) {
		//echo "用户名已被占用，请重新输入！";
		echo $sig;
		return;
	}

//----------------------------------------------------------------------------
	$line = array();
	$line[0] = <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" http-equiv="X-UA-Compatible" content="IE=edge">
		<title>SWAYTECH</title>
		<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
		<style type='text/css'>
			html{-webkit-text-size-adjust:none}
			body{margin:0 auto;color:#333;background:rgb(8,43,81);font-size:14px;font-family:"Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif;line-height:24px;cursor:default}	
			#header{line-height:66px;text-align:center;vertical-align:middle;width:100%;height:66px;background:#000}
			#header img{margin-left:10%;height:100%;float:left}
			.main{width:100%;}
			h2{padding:30px;width:100%;color:#fff;text-align:center;}
			.foot{position:absolute;bottom:0px;vertical-align:middle;margin:0 10%;padding-top:5px;width:80%;height:40px;line-height:12px;font-size:12px;color:#909090;border-top:solid 1px #E0E0E0}
			.foot span{line-height:40px}
			.foot .ICP{float:right}
		</style> 
	</head>
	<body>
		<div id='header'><img src='../../../image/login/swaytech.png'/></div>
	
<?php
	require_once( '../../php-lib/codec_lib.php' );
	\$config = read_config( '../../php-lib/config.cf' );
	\$mysql_user = \$config->user;
	\$mysql_pass = \$config->pass;
EOD;
	
	$line[1] = "\$t1 = ".time().";\r\n";
	$line[2] = "\$uid = '".$sig."';\r\n";
	$line[3] = <<<EOD
\$now = time();
if( (\$now-\$t1)>30*60 ) { // 超时了
	echo "注册超时，请重新注册！";
	unlink(__FILE__);
	return;
}
			
\$sql_con = touch_mysql();
if( empty(\$sql_con) )
	exit;
\$query_str = "UPDATE user_db.user_table SET state='unknown' WHERE uid='".\$uid."'";
mysql_unbuffered_query( \$query_str, \$sql_con );
mysql_close( \$sql_con );

echo "<h2>已经完成激活，请重新登录！</h2>";
echo '<script>setTimeout( "javascript:location.href='."'../../../login.html'".'", 3000 ); </script>';
echo '<div class="foot"><span>&copy;2014 - 2015 Swaytech All Right Reserved.</span><span class="ICP">蜀ICP备14031015号-1</span></div></body></html>';

unlink(__FILE__);

function touch_mysql() {
	global \$mysql_user, \$mysql_pass;
	\$con = mysql_connect( 'localhost', \$mysql_user, \$mysql_pass );
	if( !\$con )
		die( 'Could not connect: ' . mysql_error() );	
	mysql_unbuffered_query( "SET NAMES 'utf8'", \$con );
	mysql_select_db( 'user_db', \$con );
	return \$con;
}
?>
EOD;
//------------------------------------------------------------------------------------
// 产生用户帐户激活链接
	$active_link_dir = 'active_link_dir';
	if( !is_dir($active_link_dir) )
		mkdir( $active_link_dir );
	
	$file_name = time().'-'.rand(0,time()).'-'.rand();
	$file_name = $active_link_dir.'/'.base64_encode( $file_name ).'.php';
	
	$fp = fopen( $file_name, "w+" ); 
	if( !is_writable($file_name) )
		  die("file:" .$file_name. "Unwriteable! \r\n");

	fwrite( $fp, $line[0].$line[1].$line[2].$line[3] );
	fclose( $fp );  //关闭指针

//---------------------------------------------------------------------------------
//  发送邮件
$body = '<a href="http://'.$domain.'/'.'my-php/login/'.$file_name.'" target="_blank">'.$file_name.'</a>';
echo $body."\r\n";
$res = send_mail( $frommail, $_POST['user'], "成都实唯物联平台-用户激活", $body ); 
echo ($res-0)."\r\n";

//-----------------------------------------------------------------------------
function touch_mysql() {

	global $mysql_user, $mysql_pass;
	
	$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
	if( !$con )
		die( 'Could not connect: ' . mysql_error() );
		
	mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
	mysql_select_db( 'user_db', $con );
	return $con;
}

function send_mail($frommail,$tomail,$subject,$body) {  

    require_once( 'class.phpmailer.php' );
    include( 'class.smtp.php' );
	
	$mail = new PHPMailer();  
	$mail->IsSMTP();                            // 经smtp发送 
	$mail->CharSet = "UTF-8";	
	$mail->Host     = "smtp.mxhichina.com";           // SMTP 服务器  
	$mail->SMTPAuth = true;                     // 打开SMTP 认证  
	$mail->Username = "swaylink@cdsway.com";    // 用户名  
	$mail->Password = "Sway2012";          // 密码  
	$mail->From     = $frommail;                  // 发信人  
	$mail->FromName = "swaylink@cdsway.com";        // 发信人别名  
	$mail->AddAddress( $tomail );                 // 收信人  
	$mail->IsHTML(true); 
 
	$mail->WordWrap = 50;  
	$mail->Subject  = $subject;                 // 邮件标题  
	$mail->Body     = $body;                    // 邮件内空  
	$mail->AltBody  =  "请使用HTML方式查看邮件。";  
	return $mail->Send();  
}
?>