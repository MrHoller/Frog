<?php

namespace mrholler\frog;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;

class FrogUtils {
  
  public static function findFrog(Player $player) : ?FrogEntity {
    foreach($player->getWorld()->getEntities() as $entity){
      if($entity instanceof FrogEntity){
        if($entity->getPlayer()->getName() == $player->getName()){
          return $entity;
        }
      }
    }
    return null;
  }
  
  public static function getOptions(Player $player) : array {
    $default = ["nametag" => "Frog", "lvl" => 0, "isAttack" => false, "isSpawned" => false];
    if(!Frog::$config->get($player->getName(), false)){
      Frog::$config->set($player->getName(), $default);
      Frog::$config->save();
      Frog::$config->reload();
    }
    return Frog::$config->get($player->getName());
  }
  
  public static function setOption(Player $player, string $name, mixed $value) : void {
    Frog::$config->setNested($player->getName().".".$name, $value);
    Frog::$config->save();
    Frog::$config->reload();
  }
  
  public static function sendToast(Player $player, string $title, string $message) : void {
    $pk = ToastRequestPacket::create($title, $message);
    $player->getNetworkSession()->sendDataPacket($pk);
  }
  
}
