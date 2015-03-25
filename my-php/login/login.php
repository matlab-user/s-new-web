<?php
	require_once( "../php-lib/codec_lib.php" );
	
	$config = read_config( '../php-lib/config.cf' );
	$mysql_user = $config->user;
	$mysql_pass = $config->pass;

	if( !( isset($_POST['user']) & isset($_POST['passwd']) ) )
		exit;

?>