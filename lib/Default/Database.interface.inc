<?php
interface Database {
	public function query($query);
	
	public function nextRecord();
	
	public function getField($name);
	
	public function getRow();
	
	public function lockTable($table);
	
	public function unlock();
	
	public function getNumRows();
	
	public function getInsertID();
	
	public function escapeString($string,$quotes=true,$nullable=false);
	
	public function escapeBoolean($bool);
}
