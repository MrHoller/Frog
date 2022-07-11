<?php

namespace mrholler\frog;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class FrogCommand extends Command {

    public function __construct(){
        parent::__construct("frog");
        $this->setDescription("Settings and spawn frog");
        $this->setUsage("/frog to open form");
        $this->setPermission("mrholler.frog");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
      
      if(!$sender instanceof Player){
        $sender->sendMessage("Use the command in the game");
        return false;
      }
      
      if($sender->hasPermission("mrholler.frog") or Server::getInstance()->isOp($sender->getName())){
        FrogForm::open($sender);
        return true;
      } else {
        $sender->sendMessage("You don't have enough rights");
        return false;
      }
     
   }

}
