<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.include_template.php
 * Type:     function
 * Name:     include_template
 * Purpose:  outputs a template file based on input
 * -------------------------------------------------------------
 */
function smarty_function_include_template($params, &$smarty)
{
    if (empty($params['template']))
    {
        $smarty->trigger_error("include_template: missing 'template' parameter");
        return;
    }
    if (empty($params['assign']))
    {
        $smarty->trigger_error("include_template: missing 'assign' parameter");
        return;
    }
    $smarty->assign($params['assign'],get_template_loc($params['template']));
}
?> 