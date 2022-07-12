<?php

namespace mrholler\frog;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\Server;
use pocketmine\permission\PermissionManager;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Entity;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;


class Frog extends PluginBase implements Listener {
  
  public static Config $config;

  public function onEnable() : void {
    self::$config = new Config($this->getDataFolder()."/frogs.yml", Config::YAML);
    EntityFactory::getInstance()->register(FrogEntity::class, function(World $world, CompoundTag $nbt) : Entity {
      return new FrogEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
    }, ["Frog", "minecraft:frog"]);
    PermissionManager::getInstance()->addPermission(new Permission("mrholler.frog"));
    Server::getInstance()->getCommandMap()->registerAll("mrholler", [new FrogCommand()]);
    Server::getInstance()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function onQuit(PlayerQuitEvent $event) : void {
    $player = $event->getPlayer();
    $opts = FrogUtils::getOptions($player);
    if($opts["isSpawned"]){
      FrogUtils::setOption($player, "isSpawned", false);
    }
  }

}
