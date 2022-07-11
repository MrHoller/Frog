<?php

declare(strict_types=1);

namespace mrholler\frog;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntitySizeInfo;

class FrogEntity extends Living{

	private float $speed = 1.0;
	private bool $moving = false;
	protected $jumpVelocity = 0.6;
	private int $lvl = 0;
	private array $inventory = [];
	private string $nametag = "Frog";
	private ?Player $player = null;

	public static function getNetworkTypeId() : string{ return "minecraft:frog"; }
	
	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.55, 0.5);
	}

	public function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(24);
		parent::initEntity($nbt);
		$this->setNameTag($this->nametag." L.".$this->lvl);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function getName() : string{
		return "Frog";
	}

	public function getDrops() : array{
		return $this->inventory;
	}

	public function getXpDropAmount() : int{
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
		$playerLocation = $this->getPlayer()->getLocation();
		$entityLocation = $this->getLocation();
		if($this->moving){
		  $inWater = $this->isUnderwater();
			$this->setHasGravity(!$inWater);
			$x = $playerLocation->x - $entityLocation->x;
			$z = $playerLocation->z - $entityLocation->z;
			$diff = abs($x) + abs($z);
			if($diff != 0){
			  $this->lookAt($playerLocation->add(0, 1, 0));
			  if($entityLocation->getWorld()->getBlock($entityLocation->addVector($this->getDirectionVector()))->isSolid()){
			    $this->jump();
			  }
				$motion = ($this->onGround ? 0.125 : 0.001) * $this->getSpeed() * $tickDiff / $diff;
				$this->motion->x += $x * $motion;
				$this->motion->z += $z * $motion;
			}
			
			if($playerLocation->distance($entityLocation) <= 2){
				$this->moving = false;
			}
		} else {
			if($playerLocation->distance($entityLocation) >= 2){
			  if($playerLocation->distance($entityLocation) >= 20){
			    $this->teleport($playerLocation);
			  }
			  $this->moving = true;
			}
		}
		return $hasUpdate;
	}

	protected function onDeath() : void{
		parent::onDeath();
		FrogForm::setOption($this->getPlayer(), "isSpawned", false);
		Frog::sendToast($this->getPlayer(), "Frog", "Your frog is dead");
		$this->flagForDespawn();
	}
	
	public function getSpeed() : float{
		return $this->speed;
	}
	
	public function updateNameTag(string $nametag) : void {
	  $this->nametag = $nametag;
	  $this->setNameTag($this->nametag." L.".$this->lvl);
	}

	public function setPlayer(?Player $player) : void{
		$this->player = $player;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

}

