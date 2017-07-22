<?php

abstract class SmrPlanetType {
	abstract protected function name();
	abstract protected function imageLink();
	abstract protected function description();
	abstract protected function maxAttackers();
	abstract protected function maxLanded();
}

class TerranPlanet extends SmrPlanetType {
	public function name()         { return "Terran Planet"; }
	public function imageLink()    { return "images/planet1.png"; }
	public function description()  { return "A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms."; }
	public function maxAttackers() { return 10; }
	public function maxLanded()    { return 0; }
}

class AridPlanet extends SmrPlanetType {
	public function name()         { return "Arid Planet"; }
	public function imageLink()    { return "images/planet2.png"; }
	public function description()  { return "A world mostly devoid of surface water, but capable of supporting life."; }
	public function maxAttackers() { return 5; }
	public function maxLanded()    { return 5; }
}

class DwarfPlanet extends SmrPlanetType {
	public function name()         { return "Dwarf Planet"; }
	public function imageLink()    { return "images/planet3.png"; }
	public function description()  { return "A smaller than usual planet, with no native life present."; }
	public function maxAttackers() { return 5; }
	public function maxLanded()    { return 0; }
}

class DefenseWorld extends SmrPlanetType {
	public function name()         { return "Defense World"; }
	public function imageLink()    { return "images/planet4.png"; }
	public function description()  { return "A fully armed and operational battle station loaded with excessive firepower. Attack at your own risk."; }
	public function maxAttackers() { return 10; }
	public function maxLanded()    { return 0; }
}

?>
