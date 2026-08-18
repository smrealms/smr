<?php declare(strict_types=1);

use Smr\Pages\Login\ResetPasswordRenderer;
use Smr\Pages\Login\SkeletonRenderer;
use Smr\Template;

try {
	require_once('../bootstrap.php');

	$body = fn() => ResetPasswordRenderer::render();
	Template::getInstance()->display(
		fn() => SkeletonRenderer::render($body),
	);

} catch (Throwable $e) {
	handleException($e);
}
