<?php


namespace NLevel\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use NLevel\NLevel;
use NLevel\event\LevelUpEvent;

class LevelUpItemCommand extends Command implements Listener
{
	
	protected $plugin = null;
	
	public const PERMISSION = "op";
	
	
	public function __construct (NLevel $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("레벨업권", "레벨업권 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::PERMISSION)) {
				if (isset ($args [0]) and is_numeric ($args [0])) {
					$item = Item::get (Item::PAPER, 1, 1);
					$item->setCustomName ("§f레벨업권 (+§b{$args [0]}§f)");
					$item->setLore ([
						"§r§f터치시 §b+{$args [0]}§f 만큼의 레벨이 증가합니다."
					]);
					$item->setNamedTagEntry (new IntTag ("LevelUp", $args [0]));
					$player->getInventory ()->addItem ($item);
					NLevel::message ($player, "인벤토리를 확인해주세요!");
				} else {
					NLevel::message ($player, "/레벨업권 (증가 레벨)");
				}
			} else {
				NLevel::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
			}
		} else {
			NLevel::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
	
	public function onInteract (PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer ();
		$item = $player->getInventory ()->getItemInHand ();
		
		if (!is_null ($item->getNamedTagEntry ("LevelUp"))) {
			$name = $player->getName ();
			$level = (int) $item->getNamedTagEntry ("LevelUp")->getValue ();
			for ($i=0; $i<$level; $i++) {
				$this->plugin->addLevel ($name, 1);
			}
			NLevel::message ($player, "레벨업권을 사용했습니다.");
			$player->getInventory ()->setItemInHand ($player->getInventory ()->getItemInHand ()->setCount ($item->getCount () - 1));
		}
	}
	
	public function onLevelUp (LevelUpEvent $event): void
	{
		$name = $event->getName ();
		
		if (($player = $this->plugin->getServer ()->getPlayer ($name)) instanceof Player) {
			NLevel::message ($player, "레벨이 증가하셨습니다! §aLv. " . $this->plugin->getLevel ($name) . "§7   [스탯 포인트 +2]");
			$player->addTitle ("§l§6Level §fU§6P !!", "§7레벨§a Lv. " . $this->plugin->getLevel ($name) . "§7 을(를) 달성하셨습니다.", 20, 60, 20);
		}
		\NStatus\NStatus::runFunction ()->addStatPoint ($name, 2);
	}
	
}