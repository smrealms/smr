<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.checkdisableajax.php
 * Type:     modifier
 * Name:     checkdisableajax
 * Purpose:  check for things we don't want refreshed with AJAX
 * -------------------------------------------------------------
 */
function smarty_modifier_checkdisableajax($string)
{
	return preg_match('/<input'.'[^>]*'.'[^(submit)(hidden)(image)]'.'[^>]*'.'>/i', $string)!=0;
}
?>