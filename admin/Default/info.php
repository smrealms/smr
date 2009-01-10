<?

$smarty->assign('PageTopic','CHECKING INFO');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'info.php';
if (isset($_REQUEST['number'])) $number = $_REQUEST['number'];
$login = $_REQUEST['login'];
if (isset($number))
    $container['number'] = $number;
$u = 3;
if (!isset($number) && !isset($var['number'])) {

    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=('How many player\'s info do you need to check?<br />');
    $PHP_OUTPUT.=('<input type="text" name="number" maxlength="5" size="5" id="InputFields" style="text-align:center;"><br />');
    $PHP_OUTPUT.=create_submit('Next Page');
    $PHP_OUTPUT.=('</form>');

} elseif (!isset($login)) {

    $PHP_OUTPUT.=create_form_parameter($container, 'name="form_inf"');
    $i = 0;
    $PHP_OUTPUT.=('Enter the login names in the following boxes please.<br />');
    while ($i < $number) {

        $PHP_OUTPUT.=('<input type="text" name="login['.$i.']" maxlength="35" size="35" id="InputFields" style="text-align:center;">');
        $i ++;
        $PHP_OUTPUT.=('<br /><br />');

    }
    $PHP_OUTPUT.=('<br />');
    $PHP_OUTPUT.=create_submit('Check');
    $PHP_OUTPUT.=('</form>');

} else {

	if (isset($var['number'])) $number = $var['number'];
    $db2 = new SMR_DB();
    $db3 = new SMR_DB();
    $container = array();
    $container['url'] = 'info_proc.php';
    $PHP_OUTPUT.=create_form_parameter($container, 'name="form_inf"');
    $PHP_OUTPUT.=('<input type=hidden value=0 name=buttons>');
    $PHP_OUTPUT.=('<input type=hidden value=0 name=buttons2>');
    echo_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center" nowrap>Name</th>');
    $PHP_OUTPUT.=('<th align="center" nowrap>City & Email</th>');
    $PHP_OUTPUT.=('<th align="center" nowrap>Disabled Info</th>');
    $PHP_OUTPUT.=('<th algin="center" nowrap>Exception</th>');
    $PHP_OUTPUT.=('<th algin="center" nowrap>Ban</th>');
    $PHP_OUTPUT.=('</tr>');
    foreach ($login as $name) {

        $db->query('SELECT * FROM account WHERE login = '.$db->escapeString($name));

        if ($db->next_record()) {

            $PHP_OUTPUT.=('<tr>');
            $aname = $db->f('first_name');
            $login_name = $db->f('login');
            $aname .= '&nbsp;';
            $aname .= $db->f('last_name');
            $city = $db->f('city');
            $email = $db->f('email');
            $id = $db->f('account_id');
            $PHP_OUTPUT.=('<td align="center">'.$aname.'<br />'.$name.'<br />Account:'.$id.'</td>');
            $PHP_OUTPUT.=('<td align="center">'.$city.'<br />'.$email.'</td>');
			//check who they match...first find out the method.
            $db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$id);
            if ($db2->next_record()) $reason = $db2->f('suspicion');
            list ($method, $info) = split (':', $reason);
            if ($method == 'Match') {
            	
            	//this stops loops evetually
            	$done = array();
            	$listed = array();
            	//Entered via ip search
            	$PHP_OUTPUT.=('<td>User closed in big IP Search or Edit Account matching ');
            	//this is who they initially match
            	$curr_account =& SmrAccount::getAccount($info);
            	$PHP_OUTPUT.=($curr_account->login);
            	$listed[] = $info;
            	//who matches them
            	$db2->query('SELECT * FROM account_is_closed WHERE suspicion = \'Match:'.$account->account_id.'\'');
            	while ($db2->next_record()) {
            		
            		$curr_account =& SmrAccount::getAccount($db2->f('account_id'));
	            	$PHP_OUTPUT.=(', '.$curr_account->login);
	            	$listed[] = $db2->f('account_id');
	            	//add this acc to the search one
	            	$search[] = $db2->f('account_id');
	            	
	            }
	            //of course we have to check the guy that he matches too
	            $search[] = $info;
	            //now we check all the others
	            while (sizeof($search) > 0) {
	            	
	            	$info = array_shift($search);
	            	//prevent infinite loops
	            	if (in_array($info, $done)) continue;
	            	$done[] = $info;
	            	$db2->query('SELECT * FROM account_is_closed WHERE suspicion = \'Match:'.$info.'\' AND account_id != '.$id);
					while ($db2->next_record()) {
            		
	            		$curr_account =& SmrAccount::getAccount($db2->f('account_id'));
		            	if (!in_array($db2->f('account_id'),$listed)) $PHP_OUTPUT.=(', '.$curr_account->login);
		            	$listed[] = $db2->f('account_id');
		            	//add this acc to the search one
		            	$search[] = $db2->f('account_id');
		            	
		            }
		            //another way to search it...
		            $db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$info.' AND account_id != '.$id);
					while ($db2->next_record()) {
            		
	            		$curr_account =& SmrAccount::getAccount($db2->f('account_id'));
		            	if (!in_array($db2->f('account_id'),$listed)) $PHP_OUTPUT.=(', '.$curr_account->login);
		            	$listed[] = $db2->f('account_id');
		            	//get this accs match
		            	$reason = $db2->f('suspicion');
            			list ($method, $info_2) = split (':', $reason);
            			if ($method == 'Match') $search[] = $info_2;
		            	
		            }

				}
				$PHP_OUTPUT.=('.</td>');
            	
            } elseif ($method == 'Match list') {
            	
            	//entered via Multi Tools
            	//check how it is listed...do we have - or , to separate
            	$sql = 'SELECT '.$info.' LIKE \'%,%\'';
				$db2->query($sql);
				$db2->next_record();
				$sql = 'SELECT '.$info.' LIKE \'%-%\'';
				$db3->query($sql);
				$db3->next_record();
				if ($db2->f(0) == 1) {
					
					//this is the ip search way
					$users = explode(',', $info);
					$PHP_OUTPUT.=('<td align=center>User IP was found to match ');
					
				} elseif ($db3->f(0) == 1) {
					
					//this is the comp share way
					$users = explode('-', $info);
            		$PHP_OUTPUT.=('<td align=center>User was found to share comp with ');

            	} else {
            		
            		//the admin closed (Edit account_account way)
            		$users = explode('+', $info);
            		$PHP_OUTPUT.=('<td align=center>User was closed via Edit Account with ');
            		
            	}
            	$size = sizeof($users);
				foreach ($users as $key => $value) {
					
					$curr_account =& SmrAccount::getAccount($value);
					if ($curr_account->account_id != $id) {
						
						$PHP_OUTPUT.=($curr_account->login);
						if ($key + 1 < $size) $PHP_OUTPUT.=(', ');
						
					}
					
				}
				$PHP_OUTPUT.=('.</td>');
            } elseif ($method == 'Auto') {
            	
            	//closed by admin with multi tools
            	$PHP_OUTPUT.=('<td align=center>Closed by Admin After viewing the accounts IPs</td>');	
            	
            } else {
            	
            	//method unsupported for lookup
            	$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$id);
            	if ($db2->next_record())
            		$PHP_OUTPUT.=('<td align=center>'.$method.', '.$info.', '.$reason.'.The method this account was closed with is not supported by Info Check</td>');
            	else $PHP_OUTPUT.=('<td align=center>This account is not closed</td>');
            	
            }
            echo'
			<SCRIPT LANGUAGE=JavaScript>
			
			function go(e, ty) {
				var base = document.form_inf;
				var buttons_d = base.buttons.value;
				var u = e;
				var t = 0;
				var len = base.elements.length;
				for (var i = ty; i < len; i++) {
					var e = base.elements[i];
				    
				    if (e.name != "action") {
						if (u.checked) {
							e.disabled=false;
							e.value=\'Enter Reason\';
							e.select();
							e.focus();
							var inc = -1;
							for (var o = 1; o <= $number; o++) {
								var curr = 2 + (o * 4);
								base.elements[curr].checked=false;
								var curr = curr - 1;
								base.elements[curr].value=\'Check Box Below\';
								base.buttons2.value = 0;
							}
						} else {
							e.disabled=true;
							e.value=\'Check Box Below\';
							var inc = 1;
						}
						var i = len - 3;
						var buttons_d = buttons_d - inc;
						base.buttons.value = buttons_d;
					}
					if (e.name == "action") {
						if (u.checked) {
							if (t == 0) e.value=\'Reopen and Add Exception\';
							else e.value = \'Reopen without Exception\';
						} else {
							if (buttons_d == 0) {
								if (t == 0) e.value=\'Select an Option\';
								else e.value = \'Select an Option\';
							}
						}
						var t = 1;
					}
				}
			}
			
			function go2(e, ty) {
				var base = document.form_inf;
				var buttons_e = base.buttons2.value;
				var u = e;
				var t = 0;
				var len = base.elements.length;
				for (var i = ty; i < len; i++) {
				    var e = base.elements[i];
				    if (e.name != "action") {
						if (u.checked) {
							e.disabled=false;
							e.value=\'Match list:\';
							alert("Please enter the closing data using the following syntax: \'Match list:1+5+78\' Thanks");
							e.focus();
							var inc = -1;
							for (var o = 1; o <= $number; o++) {
								var curr = o * 4;
								base.elements[curr].checked=false;
								var curr = curr - 1;
								base.elements[curr].value=\'Check Box Below\';
								base.buttons.value = 0;
							}
						} else {
							e.disabled=true;
							e.value=\'Check Box Below\';
							var inc = 1;
						}
						var i = len - 3;
						var buttons_e = buttons_e - inc;
						base.buttons2.value = buttons_e;
					}
					if (e.name == "action") {
						if (u.checked) {
							if (t == 0) e.value=\'Ban\';
							else e.value = \'Ban and remove exception\';
						} else {
							if (buttons_e == 0) {
								if (t == 0) e.value=\'Select an Option\';
								else e.value = \'Select an Option\';
							}
						}
						var t = 1;
					}
				}
			}</script>';
            $account_wanted = $id;
            $value = 'Check Box Below';
            $db3->query('SELECT * FROM account_exceptions WHERE account_id = '.$account_wanted);
            if ($db3->next_record())
            	$value = $db3->f('reason');
            $PHP_OUTPUT.=('<td align=center><input type="text" name="exception['.$account_wanted.']" value="$value" size="15" id="InputFields" disabled><br /><input onclick=go(this,'.$u.') type="checkbox" name="account_id[]" value="'.$account_wanted.'"></td>');
            $u += 2;
            $value2 = 'Check Box Below';
            $db3->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$account_wanted);
            if ($db3->next_record())
            	$value2 = $db3->f('reason');
            $PHP_OUTPUT.=('<td align="center">');
            $PHP_OUTPUT.=('<input type=text name="ban['.$account_wanted.']" value="'.$value2.'" size=15 id=Inputfields disabled><br /><input type=checkbox name="bancheck[]" value="'.$account_wanted.'" onclick=go2(this,'.$u.')>');
            $PHP_OUTPUT.=('&nbsp;');
            $PHP_OUTPUT.=('</td>');
            $u += 2;
            $PHP_OUTPUT.=('</tr>');
        } else {
            $PHP_OUTPUT.=('<tr>');
            $PHP_OUTPUT.=('<td align="center" colspan="7">The login '.$name.' doesn\'t exist</td>');
            $PHP_OUTPUT.=('</tr>');
        }
    } //end foreach
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td align="center" colspan=3>');
    $PHP_OUTPUT.=create_submit('Select an Option');
    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td align="center" colspan=2>');
    $PHP_OUTPUT.=create_submit('Select an Option');
    $PHP_OUTPUT.=('</td></tr>');
    $PHP_OUTPUT.=('</table>');
} //end else

?>