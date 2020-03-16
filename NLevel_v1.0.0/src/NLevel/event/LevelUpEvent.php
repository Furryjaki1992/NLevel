<?php


namespace NLevel\event;


use pocketmine\event\Event;


class LevelUpEvent extends Event
{
	
	protected $name = "";
	
	
	public function __construct (string $name)
	{
		$this->name = $name;
	}
	
	public function getName (): string
	{
		return $this->name;
	}
}