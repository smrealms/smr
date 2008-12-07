<?php
interface Database
{
	function query($query);
	
	function nextRecord();
	
	function getField($name);
	
	function getRow();
	
	function lockTable($table);
	
	function unlock();
	
	function getNumRows();
	
	function insertId();
	
	function error($err);
	
	//  function free();
	
	function escapeString($string,$quotes=true);
}
?>
