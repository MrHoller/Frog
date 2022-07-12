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
    if(FrogUtils::getOptions($player)["isSpawned"] and FrogUtils::findFrog($player) instanceof FrogEntity){
      $form->addButtonEasy("Despawn");
    } else {
      $form->addButtonEasy("Spawn");
    }
    $form->setCallable(function(Player $player, $data) : void {
      if($data == "Despawn"){
        $entity = FrogUtils::findFrog($player);
        if($entity instanceof FrogEntity){
          $entity->flagForDespawn();
          Frog::sendToast($player, "Frog", "Despawned");
          FrogUtils::setOption($player, "isSpawned", false);
        }
      }
      if($data == "Spawn"){
        $options = FrogUtils::getOptions($player);
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
        FrogUtils::setOption($player, "isSpawned", true);
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
    //$form->addInput(FrogUtils::getOptions($player)["nametag"], "Name frog", FrogUtils::getOptions($player)["nametag"]);
    $form->addToggle("Attack", FrogUtils::getOptions($player)["isAttack"]);
    $form->setCallable(function(Player $player, $data) : void {
      /*
      TODO :(
      if(isset($data[0])){
        if(!empty($data[0]) and strlen($data[1]) >= 3){
          FrogUtils::setOption($player, "nametag", $data[0]);
        }
      }*/
      if(isset($data[0])){ // 1
        FrogUtils::setOption($player, "isAttack", $data[0]); // 1
      }
      $entity = FrogUtils::findFrog($player);
      if($entity instanceof FrogEntity){
        $entity->updateFrog();
      }
      self::open($player);
    });
    $player->sendForm($form);
  }
  
}
