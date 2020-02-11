<?php declare(strict_types=1);

namespace Routes;

class MultiplePortRoute extends Route {
	private OneWayRoute $forwardRoute;
	private OneWayRoute $returnRoute;

	public function __construct(OneWayRoute $_forwardRoute, OneWayRoute $_returnRoute) {
		$this->forwardRoute = $_forwardRoute;
		$this->returnRoute = $_returnRoute;
	}

	public function getForwardRoute() : ?OneWayRoute {
		return $this->forwardRoute;
	}

	public function getReturnRoute() : ?OneWayRoute {
		return $this->returnRoute;
	}
}
