<?php declare(strict_types=1);

use Smr\Pages\Login\LoginCreateRenderer;
use Smr\Pages\Login\SkeletonRenderer;
use Smr\Template;

try {
	require_once('../bootstrap.php');

	$body = fn() => LoginCreateRenderer::render();
	Template::getInstance()->display(
		fn() => SkeletonRenderer::render($body),
	);

} catch (Throwable $e) {
	handleException($e);
}
