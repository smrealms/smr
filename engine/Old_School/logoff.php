<?
// Remove the lock if we're holding one (ie logged off from game screen)
if($lock) {
	release_lock();
}
$lock=false;
SmrSession::destroy();

?>