<?php
	session_start();
	
	if( !isset($_SESSION['m_code'])  )
		exit;
	
	echo '{"user":"'.$_SESSION['user'].'","m_code":"'.$_SESSION['m_code'].'","m_name":"'.$_SESSION['m_name'].'"}';
	

?>