<?php
/**
 * Trim whitespace
 */
function smarty_modifier_pagetrimwhitespace($string)
{
    // Pull out the script blocks
    preg_match_all("!<script[^>]*?>.*?</script>!is", $string, $match);
    $_script_blocks = $match[0];
    $string = preg_replace("!<script[^>]*?>.*?</script>!is",
                           '@@@SMARTY:TRIM:SCRIPT@@@', $string);

    // Pull out the pre blocks
    preg_match_all("!<pre[^>]*?>.*?</pre>!is", $string, $match);
    $_pre_blocks = $match[0];
    $string = preg_replace("!<pre[^>]*?>.*?</pre>!is",
                           '@@@SMARTY:TRIM:PRE@@@', $string);
    
    // Pull out the textarea blocks
    preg_match_all("!<textarea[^>]*?>.*?</textarea>!is", $string, $match);
    $_textarea_blocks = $match[0];
    $string = preg_replace("!<textarea[^>]*?>.*?</textarea>!is",
                           '@@@SMARTY:TRIM:TEXTAREA@@@', $string);

    // remove all leading spaces, tabs and carriage returns NOT
    // preceeded by a php close tag.
    $string = trim(preg_replace('/[\s]+/', ' ', $string));
    $string = trim(preg_replace('/> </', '><', $string));

    // replace textarea blocks
    smarty_modifier_pagetrimwhitespace_replace("@@@SMARTY:TRIM:TEXTAREA@@@",$_textarea_blocks, $string);

    // replace pre blocks
    smarty_modifier_pagetrimwhitespace_replace("@@@SMARTY:TRIM:PRE@@@",$_pre_blocks, $string);

    // replace script blocks
    smarty_modifier_pagetrimwhitespace_replace("@@@SMARTY:TRIM:SCRIPT@@@",$_script_blocks, $string);

    return $string;
}

function smarty_modifier_pagetrimwhitespace_replace($search_str, $replace, &$subject) {
    $_len = strlen($search_str);
    $_pos = 0;
    for ($_i=0, $_count=count($replace); $_i<$_count; $_i++)
        if (($_pos=strpos($subject, $search_str, $_pos))!==false)
            $subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
        else
            break;

}

?>
