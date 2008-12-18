<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.date.php
 * Type:     modifier
 * Name:     date
 * Purpose:  capitalize words in the string
 * -------------------------------------------------------------
 */
function smarty_modifier_date($string,$format)
{
    return date($format,$string);
}
?> 