<?

// include database class for mysql
//require_once($LIB . 'smr_db.inc');
require_once($LIB . 'global/smr_db.inc');

class SMR_HISTORY_DB extends DB_Sql {

	var $Host     = 'localhost';
	var $Database = 'smr_history';
	var $User     = 'smr';
	var $Password = 'fUKiS0Xr';

	function haltmsg($msg) {

		$PHP_OUTPUT.=('<h1>Database error:</h1> '.$msg.'<br><br><br>');
		$PHP_OUTPUT.=('<b>MySQL Error</b>: '.$this->Errno.' ('.$this->Error.')<br>'.EOL);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td width="1" bgcolor="#0B8D35"></td>');
		$PHP_OUTPUT.=('<td align="left" valign="top">');
		$PHP_OUTPUT.=('<table cellspacing="5" cellpadding="5" border="0">');
		$PHP_OUTPUT.=('<tr><td></td></tr>');
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td></td>');
		$PHP_OUTPUT.=('<td colspan="3" height="1" bgcolor="#0b8d35"></td>');
		$PHP_OUTPUT.=('<td></td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</body>');
		$PHP_OUTPUT.=('</html>');

	}

}

?>