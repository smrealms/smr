<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.assign_random.php
 * Type:     function
 * Name:     assign_random
 * Purpose:  assigns a random number using mt_rand with optional min and max paramaters
 * -------------------------------------------------------------
 */
function smarty_function_assign_random($params, &$smarty)
{
	if (empty($params['var']))
	{
		$smarty->trigger_error("include_template: missing 'var' parameter");
		return;
	}
	if (isset($params['min']) && isset($params['max']))
		$smarty->assign($params['var'],mt_rand($params['min'],$params['max']));
	else
		$smarty->assign($params['var'],mt_rand());
}
?> 