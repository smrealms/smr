<?php declare(strict_types=1);

use Smr\Pages\Login\ErrorRenderer;
use Smr\Pages\Login\SkeletonRenderer;
use Smr\Request;
use Smr\Template;

try {
	require_once('../bootstrap.php');

	$errorMessage = Request::get('msg', 'No error message found!');
	$body = fn() => ErrorRenderer::render($errorMessage);
	Template::getInstance()->display(
		fn() => SkeletonRenderer::render($body),
	);

} catch (Throwable $e) {
	handleException($e);
}
