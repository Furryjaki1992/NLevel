<?php


namespace NLevel\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerJoinEvent,
	PlayerInteractEvent
};

use NLevel\NLevel;

class LevelBuffItemCommand extends Command implements Listener
{
	
	protected $plugin = null;
	
	public const PERMISSION = "op";
	
	
	public function __construct (NLevel $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("경험치물약", "경험치물약 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::PERMISSION)) {
				if (isset ($args [0]) and is_numeric ($args [0]) and isset ($args [1]) and is_numeric ($args [1])) {
					$item = Item::get (384, 1, 1);
					$item->setCustomName ("§f경험치 {$args [0]}배 물약({$args [1]}초)");
					$item->setLore ([
						"§r§f터치시 획득 경험치량이 {$args [1]}초 동안 §b{$args [0]}배§f가 증가합니다.",
						"",
						"§r§b*§f 기존 섭취중인 물약 버프가 끝나야 사용이 가능합니다. §b*"
					]);
					$item->setNamedTagEntry (new IntTag ("BuffAmf", $args [0]));
					$item->setNamedTagEntry (new IntTag ("BuffTime", $args [1]));
					$player->getInventory ()->addItem ($item);
					NLevel::message ($player, "인벤토리를 확인해주세요!");
				} else {
					NLevel::message ($player, "/경험치물약 (배) (초)");
				}
			} else {
				NLevel::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
			}
		} else {
			NLevel::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
	
	public function onJoin (PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer ();
		
		if (!$this->plugin->isRegistered ($player->getName ())) {
			$this->plugin->addRegister ($player->getName ());
		}
	}
	
	public function onInteract (PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer ();
		$item = $player->getInventory ()->getItemInHand ();
		
		if (!is_null ($item->getNamedTagEntry ("BuffAmf"))) {
			if (!isset ($this->plugin->db ["buff"] [$player->getName ()])) {
				$amf = (int) $item->getNamedTagEntry ("BuffAmf")->getValue ();
				$time = (int) $item->getNamedTagEntry ("BuffTime")->getValue ();
				
				$this->plugin->db ["buff"] [$player->getName ()] = [
					"amf" => $amf,
					"time" => $time
				];
				NLevel::message ($player, "경험치 물약을 사용했습니다.");
				$player->getInventory ()->setItemInHand ($player->getInventory ()->getItemInHand ()->setCount ($item->getCount () - 1));
			} else {
				NLevel::message ($player, "이미 다른 버프를 받는 중 입니다.");
			}
		}
	}
	
}