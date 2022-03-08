<?php declare(strict_types=1);

namespace Routes;

class MultiplePortRoute extends Route {

	public function __construct(
		private Route $forwardRoute,
		private OneWayRoute $returnRoute,
	) {}

	public function getForwardRoute() : ?Route {
		return $this->forwardRoute;
	}

	public function getReturnRoute() : ?OneWayRoute {
		return $this->returnRoute;
	}
}
