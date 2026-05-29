<?php

declare(strict_types=1);

namespace RenamingUI;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RenameCommand extends Command{

    public function __construct(){
        parent::__construct("renameui", "Open Rename UI");
        $this->setPermission("renamingui.use");
        $this->setAliases(["rename"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
        if(!$sender instanceof Player){
            return;
        }

        if(!$this->testPermission($sender)){
            return;
        }

        Forms::sendItemSelectForm($sender);
    }
}
