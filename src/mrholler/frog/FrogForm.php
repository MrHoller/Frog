<?php

namespace mrholler\frog;

use pocketmine\player\Player;
use mrholler\frog\libs\xenialdan\customui\windows\SimpleForm;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\EntityDataHelper;

class FrogForm {
  
  public static function open(Player $player) : void {
    $form = new SimpleForm("Menu Frog");
    // TODO : RENAME FROG
    // TODO : SWITCH ATTACKING FROG
    // TODO : UP LVL FROG
    $form->addButtonEasy(self::getOptions($player)["isSpawned"] ? "Despawn" : "Spawn");
    $form->setCallable(function(Player $player, $data) : void {
      if($data == "Despawn"){
        $world = $player->getWorld();
        foreach($world->getEntities() as $entity){
          if($entity instanceof FrogEntity){
            if($entity->getPlayer()->getName() == $player->getName()){
              $entity->flagForDespawn();
              Frog::sendToast($player, "Frog", "Despawned");
              self::setOption($player, "isSpawned", false);
              return;
            }
          }
        }
      }
      if($data == "Spawn"){
        $options = self::getOptions($player);
        $location = $player->getLocation();
	      $nbt = CompoundTag::create()
          ->setTag("Pos", new ListTag([
                new DoubleTag($location->x),
                new DoubleTag($location->y),
                new DoubleTag($location->z)
            ]))
          ->setTag("Motion", new ListTag([
                new DoubleTag(0.0),
                new DoubleTag(0.0),
                new DoubleTag(0.0)
            ]))
          ->setTag("Rotation", new ListTag([
                new FloatTag($location->yaw),
                new FloatTag($location->pitch)
            ]));
	      $frog = new FrogEntity(EntityDataHelper::parseLocation($nbt, $location->getWorld()), $nbt);
        $frog->setPlayer($player);
        $frog->updateNameTag($options["nametag"]);
        $frog->spawnToAll();
        Frog::sendToast($player, "Frog", "Spawned");
        self::setOption($player, "isSpawned", true);
        return;
      }
    });
    $player->sendForm($form);
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
  
}
