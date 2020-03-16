<?php

namespace NLevel\task;

use pocketmine\scheduler\Task;
use NLevel\NLevel;

class LevelBuffTask extends Task
{
	
	protected $plugin;
	
	
	public function __construct (NLevel $plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function onRun (int $currentTick)
	{
		foreach ($this->plugin->getServer ()->getOnlinePlayers () as $player) {
			$name = $player->getName ();
			if (isset ($this->plugin->db ["buff"] [$name])) {
				$this->plugin->db ["buff"] [$name] ["time"] --;
				if ($this->plugin->db ["buff"] [$name] ["time"] <= 0) {
					unset ($this->plugin->db ["buff"] [$name]);
					NLevel::message ($player, "경험치 버프 사용시간이 끝났습니다.");
				}
			}
		}
	}
}