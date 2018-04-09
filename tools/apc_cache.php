<?php
if (!function_exists('apc_compile_file')) {
    echo "ERROR: apc_compile_file does not exist!";
    exit(1);
}
 
 
/**
 * Compile Files for APC
 * The function runs through each directory and
 * compiles each *.php file through apc_compile_file
 * @param string $dir start directory
 * @return void
 */
function compile_files($dir,$exts=array('*.php'))
{
    $dirs = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
    if (is_array($dirs) && count($dirs) > 0) {
        while(list(,$v) = each($dirs)) {
            compile_files($v,$exts);
        }
    }
	
	foreach($exts as $ext)
	{
		echo "\n\n".'Compiling '.$ext."\n";
		$files = glob($dir . DIRECTORY_SEPARATOR . $ext);
		if (is_array($files) && count($files) > 0)
		{
			while(list(,$v) = each($files))
			{
				echo 'Compiling '.$v."\n";
				apc_compile_file($v);
			}
		}
	}
}
 
compile_files('/home/page/SMR',array('*.inc','*.php'));
