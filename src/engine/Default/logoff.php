<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$account->log(LOG_TYPE_LOGIN, 'logged off from ' . getIpAddress());

// Remove the lock if we're holding one (ie logged off from game screen)
if ($lock) {
	release_lock();
}
$session->destroy();

// Send the player back to the login screen
$msg = 'You have successfully logged off!';
header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
exit;
