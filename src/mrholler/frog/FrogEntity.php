<?php

declare(strict_types=1);

namespace mrholler\frog;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class FrogEntity extends Living{

	private float $speed = 1.0;
	private bool $moving = false;
	protected $jumpVelocity = 0.6;
	private int $lvl = 0;
	private array $inventory = [];
	private string $nametag = "Frog";
	private ?Player $player = null;
	private bool $isAttacking = false;
	private ?Entity $target = null;

	public static function getNetworkTypeId() : string { return "minecraft:frog"; }
	
	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo(0.55, 0.5);
	}

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
		$this->setMaxHealth(10);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function getName() : string {
		return "Frog";
	}

	public function getDrops() : array {
		return $this->inventory;
	}

	public function getXpDropAmount() : int {
		return 0;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if(!$this->getPlayer() instanceof Player){
			$this->flagForDespawn();
			return $hasUpdate;
		}
		if(!$this->isAlive()){
		  return $hasUpdate;
		}
		
		if(($item = $this->findItem()) instanceof ItemEntity){
		  $this->target = $item;
		}
		
		if($this->isAttacking and $this->target instanceof Entity){
		  $targetLocation = $this->target->getLocation();
		  if(mt_rand(1, 3) == 1){
		    $this->attackEntity($this->target);
		  }
		} else {
		  $targetLocation = $this->getPlayer()->getLocation();
		}
		$entityLocation = $this->getLocation();
		if($this->moving){
		  $inWater = $this->isUnderwater();
			$this->setSwimming($inWater);
			$x = $targetLocation->x - $entityLocation->x;
			$z = $targetLocation->z - $entityLocation->z;
			$diff = abs($x) + abs($z);
			if($diff != 0){
			  $this->lookAt($targetLocation->add(0, 1, 0));
			  if($entityLocation->getWorld()->getBlock($entityLocation->addVector($this->getDirectionVector()))->isSolid()){
			    $this->jump();
			  }
				$motion = ($this->onGround ? 0.125 : 0.001) * $this->getSpeed() * $tickDiff / $diff;
				$this->motion->x += $x * $motion;
				$this->motion->z += $z * $motion;
			}
			
			if($targetLocation->distance($entityLocation) <= 3 and !$this->target instanceof Entity){
				$this->moving = false;
			}
		} else {
			if($targetLocation->distance($entityLocation) >= 3){
			  if($targetLocation->distance($entityLocation) >= 20){
			    $this->teleport($targetLocation);
			  }
			  $this->moving = true;
			}
		}
		return $hasUpdate;
	}

	protected function onDeath() : void {
		parent::onDeath();
		FrogForm::setOption($this->getPlayer(), "isSpawned", false);
		Frog::sendToast($this->getPlayer(), "Frog", "Your frog is dead");
		$this->flagForDespawn();
	}
	
	public function getSpeed() : float {
		return $this->speed;
	}
	
	public function updateFrog() : void {
	  $this->nametag = FrogForm::getOptions($this->getPlayer())["nametag"];
		$this->isAttacking = (bool) FrogForm::getOptions($this->getPlayer())["isAttack"];
		$this->lvl = (int) FrogForm::getOptions($this->getPlayer())["lvl"];
	  $this->setNameTag($this->nametag." L. ".$this->lvl);
	}

	public function setPlayer(?Player $player) : void {
		$this->player = $player;
	}

	public function getPlayer() : ?Player {
		return $this->player;
	}
	
	public function attack(EntityDamageEvent $source) : void {
	  parent::attack($source);
	  if($source instanceof EntityDamageByEntityEvent and $this->isAttacking){
	    $this->attackEntity($source->getDamager());
	    $this->target = $source->getDamager();
	  }
	}
	
	public function attackEntity(Entity $damager) : void {
	  if($damager->isAlive() and $damager->getLocation()->distance($this->getLocation()) <= 1){
	    $ev = new EntityDamageByEntityEvent($this, $damager, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 1);
	    $damager->attack($ev);
	  } else if(!$damager->isAlive() or $damager->getLocation()->distance($this->getLocation()) >= 15){
	    $this->target = null;
	  }
	}
	
	// TODO : LVL
	public function getLvl() : int {
	  return $this->lvl;
	}

}

