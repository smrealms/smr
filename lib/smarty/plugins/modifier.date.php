<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.date.php
 * Type:     modifier
 * Name:     date
 * Purpose:  print a formatted date
 * -------------------------------------------------------------
 */
function smarty_modifier_date($string,$format)
{
	if(defined($format))
		return date(constant($format),$string);
	return date($format,$string);
}
?>