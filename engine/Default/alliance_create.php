<?php

$template->assign('PageTopic','Create Alliance');

$container = create_container('alliance_create_processing.php');
$form = create_form($container,'Create');

$PHP_OUTPUT.= $form['form'];

$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Name:</td>
		<td><input type="text" name="name" size="30"></td>
	</tr>
	<tr>
		<td class="top">Description:&nbsp;</td>
		<td><textarea spellcheck="true" name="description"></textarea></td>
	</tr>
	<tr>
		<td class="top">Password:</td>
		<td><input type="password" name="password" size="30"></td>
	</tr>
	<tr>
		<td class="top">Members start with:</td>
		<td>No permissions<input type="radio" name="Perms" value="none"><br />
			Basic permissions<input type="radio" name="Perms" value="basic" checked><br />
			Full permissions<input type="radio" name="Perms" value="full">
		</td>
	</tr>
	<tr>
		<td class="top">Recruiting:</td>
		<td>Yes<input type="radio" name="recruit" value="yes" checked><br />
			No<input type="radio" name="recruit" value="no">
		</td>
	</tr>
</table>
<br /><br />
';

$PHP_OUTPUT.= $form['submit'];

$PHP_OUTPUT.= '</form>';
