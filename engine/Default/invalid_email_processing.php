<?php

if ($_POST['action'] == "Resend Validation Code") {
	$account->changeEmail($account->getEmail());
} else {
	$account->changeEmail($_POST['email']);
}
forward(create_container('skeleton.php', 'validate.php'));
