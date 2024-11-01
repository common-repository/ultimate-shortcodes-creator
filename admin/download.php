<?php
	$file = realpath(getcwd().'/../temp/scu_backup.zip');
	header('Content-Description: File Transfer');
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="scu_backup-'.date("Ymd-His").'.zip"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header("Content-Transfer-Encoding: binary");
	header('Pragma: public');
	header('Content-Length: '.filesize($file));
	readfile($file);
	exit;
?>