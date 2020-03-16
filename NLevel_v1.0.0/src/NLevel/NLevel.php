<?php


namespace NLevel;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use pocketmine\Player;

use NLevel\command\{
	LevelBuffItemCommand,
	LevelUpItemCommand
};
use NLevel\event\LevelUpEvent;
use NLevel\task\LevelBuffTask;

class NLevel extends PluginBase
{
	
	private static $instance = null;
	
	public static $prefix = "§l§6[알림]§r§7 ";
	
	public $config, $db;
	
	
	public static function runFunction (): NLevel
	{
		return self::$instance;
	}
	
	public function onLoad (): void
	{
		if (self::$instance === null) {
			self::$instance = $this;
		}
		if (!file_exists ($this->getDataFolder ())) {
			@mkdir ($this->getDataFolder ());
		}
		$this->config = new Config ($this->getDataFolder () . "config.yml", Config::YAML, [
			"player" => [],
			"buff" => []
		]);
		$this->db = $this->config->getAll ();
	}
	
	public function onEnable (): void
	{
		foreach ([
			LevelBuffItemCommand::class,
			LevelUpItemCommand::class
		] as $class) {
			$this->getServer ()->getCommandMap ()->register ("avas", new $class ($this));
		}
		$this->getScheduler ()->scheduleRepeatingTask (new LevelBuffTask ($this), 25);
	}
	
	public function onDisable (): void
	{
		if ($this->config instanceof Config) {
			$this->config->setAll ($this->db);
			$this->config->save ();
		}
	}
	
	public function isRegistered (string $name): bool
	{
		return isset ($this->db ["player"] [$name]);
	}
	
	public function addRegister (string $name): void
	{
		$this->db ["player"] [$name] = [
			"level" => 1,
			"exp" => 0
		];
	}
	
	public function getLevel (string $name): int
	{
		if ($this->isRegistered ($name)) {
			return (int) $this->db ["player"] [$name] ["level"];
		}
		return 1;
	}
	
	public function setLevel (string $name, int $level): void
	{
		$this->db ["player"] [$name] ["level"] = $level;
	}
	
	public function getExp (string $name): int
	{
		if ($this->isRegistered ($name)) {
			return (int) $this->db ["player"] [$name] ["exp"];
		}
		return 0;
	}
	
	public function isBuff (string $name): bool
	{
		return isset ($this->db ["buff"] [$name]);
	}
	
	public function getBuffTime (string $name): int
	{
		if ($this->isBuff ($name)) {
			return $this->db ["buff"] [$name] ["time"];
		}
		return 0;
	}
	
	public function getBuffAmf (string $name): int
	{
		if ($this->isBuff ($name)) {
			return $this->db ["buff"] [$name] ["amf"];
		}
		return 1;
	}
	
	public function addExp (string $name, int $exp)
	{
		$exp = is_numeric ($exp) ? $exp : (int) $exp;
		if ($this->isBuff ($name)) {
			$exp = $exp * $this->getBuffAmf ($name);
		}
		if ($exp <= 0) {
		   $exp = 0;
		}
		$this->db ["player"] [$name] ["exp"] += $exp;
		if ($this->db ["player"] [$name] ["exp"] <= 0) {
		   $this->db ["player"] [$name] ["exp"] = 0;
		}
		if (($player = $this->getServer ()->getPlayer ($name)) instanceof Player) {
			$player->sendTip ("§l§6[§f!§6]§f  경험치 §6+{$exp}§f  |  (" . (string) $this->getExp ($name) . "/" . (($this->getLevel ($name) * 100) + 400) . ")");
		}
		//var_dump ($this->db ["player"] [$name]);
		while ($this->getExp ($name) >= ($this->getLevel ($name) * 100) + 400) {
			$this->addLevel ($name, 1);
			$this->db ["player"] [$name] ["exp"] -= ($this->getLevel ($name) * 100) + 400;
if ($this->db ["player"] [$name] ["exp"] <= 0) {
		   $this->db ["player"] [$name] ["exp"] = 0;
		}
		}
	}
	
	public function setBuff (string $name, int $amf = 1, int $time = 0): void
	{
		$this->db ["buff"] [$name] = [
			"amf" => $amf,
			"time" => $time
		];
	}
	
	public static function message ($player, string $msg): void
	{
		$player->sendMessage (self::$prefix . $msg);
	}
	
	public function addLevel (string $name, int $value = 0): void
	{
		$this->db ["player"] [$name] ["level"] += $value;
		(new LevelUpEvent ($name, $value))->call ();
	}
}