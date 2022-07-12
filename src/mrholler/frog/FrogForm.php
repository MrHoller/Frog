<?php

namespace mrholler\frog;

use pocketmine\player\Player;
use mrholler\frog\libs\xenialdan\customui\windows\SimpleForm;
use mrholler\frog\libs\xenialdan\customui\windows\CustomForm;
use mrholler\frog\libs\xenialdan\customui\elements\Button;
use mrholler\frog\libs\xenialdan\customui\elements\Toggle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\EntityDataHelper;

class FrogForm {
  
  public static function open(Player $player) : void {
    $form = new SimpleForm("Menu Frog");
    // TODO : RENAME FROG
    // TODO : UP LVL FROG
    $form->addButtonEasy("Setting");
    if(self::getOptions($player)["isSpawned"] and self::findFrog($player) instanceof FrogEntity){
      $form->addButtonEasy("Despawn");
    } else {
      $form->addButtonEasy("Spawn");
    }
    $form->setCallable(function(Player $player, $data) : void {
      if($data == "Despawn"){
        $entity = self::findFrog($player);
        if($entity instanceof FrogEntity){
          $entity->flagForDespawn();
          Frog::sendToast($player, "Frog", "Despawned");
          self::setOption($player, "isSpawned", false);
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
        $frog->updateFrog();
        $frog->spawnToAll();
        Frog::sendToast($player, "Frog", "Spawned");
        self::setOption($player, "isSpawned", true);
        return;
      }
      if($data == "Setting"){
        self::openSetting($player);
      }
    });
    $player->sendForm($form);
  }
  
  public static function openSetting(Player $player) : void {
    $form = new CustomForm("Setting frog");
    //$form->addInput(self::getOptions($player)["nametag"], "Name frog", self::getOptions($player)["nametag"]);
    $form->addToggle("Attack", self::getOptions($player)["isAttack"]);
    $form->setCallable(function(Player $player, $data) : void {
      /*
      TODO :(
      if(isset($data[0])){
        if(!empty($data[0]) and strlen($data[1]) >= 3){
          self::setOption($player, "nametag", $data[0]);
        }
      }*/
      if(isset($data[0])){ // 1
        self::setOption($player, "isAttack", $data[0]); // 1
      }
      $entity = self::findFrog($player);
      if($entity instanceof FrogEntity){
        $entity->updateFrog();
      }
      self::open($player);
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
  
}
