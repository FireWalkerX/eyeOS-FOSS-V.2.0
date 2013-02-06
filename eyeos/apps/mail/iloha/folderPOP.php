<?php
error_reporting(E_ALL & ~E_NOTICE);
require 'pop3.inc';
require_once 'icl_commons.inc';
require_once 'common.inc';
require_once '../MailBoxManager.php';
require_once 'utf8.inc';
require_once 'UTF-8.inc';

global $ICL_SSL;
$ICL_SSL = 1;
global $ICL_PORT;
$ICL_PORT = 993;
$conn = iil_pop::iil_Connect('pop.correo.yahoo.es:995', 'eyeosmail@yahoo.es', 'qazxswedc');
//$conn = iil_pop::iil_Connect('pop.gmail.com:995', 'eyeosmail@gmail.com', 'qazxswedc');
if (!$conn){
	throw new EyeException('Unable to connect to server', 0, '');
	exit;
}

//print_r(iil_pop::iil_C_FetchHeader($conn, $folder, 1));




$folder = 'INBOX';

$total_num=iil_pop::iil_C_CountMessages($conn, $folder);
if($total_num > 0) {
	$range = '1:'.$total_num;
} else {
	$range = "";
}
$headers=iil_pop::iil_C_FetchHeaders($conn, $folder, $range);
echo '<html><head><title>Browsing '.htmlentities($folder).'</title></head>';
echo '<META http-equiv="Content-Type" Content="text/html;charset=UTF-8" />';
echo '<body>';
echo '<center>';
echo '<br/>';
echo '<h2>'.htmlentities($folder).'</h2>';
echo '<br/>';
echo '<table width="800">';
echo '<tr>';
echo '	<td align="center"><b>From</b></td>';
echo '	<td align="center"><b>Subject</b></td>';
echo '</tr>';
echo '<tr>';
echo '	<td align="center">&nbsp;</td>';
echo '	<td align="center">&nbsp;</td>';
echo '</tr>';
foreach($headers as $mail) {
	$mail->from = htmlentities(LangDecodeAddressList($mail->from, 'UTF-8', ''), ENT_QUOTES, 'UTF-8');
	$mail->subject = LangDecodeSubject($mail->subject,'UTF-8');
	if($mail->seen) {
		$from = $mail->from;
		$subject = $mail->subject;
	} else {
		$from = '<b>'.$mail->from.'</b>';
		$subject = '<b>'.$mail->subject.'</b>';
	}
	$subject = '<a style="text-decoration:none;color:#333333;" href="viewmail.php?id='.$mail->id.'&folder='.htmlentities($folder).'">'.$subject.'</a>';
	echo '<tr>';
	echo '	<td>'.$from.'</td>';
	echo '	<td>'.$subject.'</td>';
	echo '</tr>';
}
echo '</table>';
echo '<br/><br/>';
echo '<a style="font-family:Verdana;color:#222222;margin-bottom:3px;text-decoration:none" href="index.php">Back</a>';
echo '</center></body></html>';
?>
