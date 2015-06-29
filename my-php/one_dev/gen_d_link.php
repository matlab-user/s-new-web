<?php
	session_start();
	
//	$_SESSION['user'] = 'free-bug@163.com';
//	$_POST['VT'] = 7;						// 有效天数
	
	if( !isset($_SESSION['user']) | !isset($_POST['VT']) )
		exit;
	
	$GEN_UTC = time();
	
	require_once( "../php-lib/codec_lib.php" );
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;
    $key = pack( 'H*', $config->key_1 );
     
	$res_str = 'http://www.swaylink.cn/devs_view.html?load=';
	// A-用户名  	B-是否运行操作    C-申请的时间      D-有效天数
    $plaintext = '{ "A":"'.$_SESSION['user'].'", "B":"NULL", "C":"'.$GEN_UTC.'", "D":"'.$_POST['VT'].'" }';

    # 为 CBC 模式创建随机的初始向量
    $iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
    $iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
    
    $ciphertext = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv );

    # 将初始向量附加在密文之后，以供解密时使用
    $ciphertext = $iv . $ciphertext;
    
    # 对密文进行 base64 编码
    $ciphertext_base64 = base64_encode( $ciphertext );

    echo  $res_str.$ciphertext_base64;

?>