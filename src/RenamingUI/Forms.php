<?php

declare(strict_types=1);

namespace RenamingUI;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\item\Durable;
use pocketmine\player\Player;

class Forms{

    public static function sendItemSelectForm(Player $player) : void{
        $plugin = Main::getInstance();

        $inventory = $player->getInventory()->getContents();

        $items = [];
        $dropdown = [];

        foreach($inventory as $slot => $item){
            if(!$item->isNull()){
                $items[] = $slot;
                $dropdown[] = $item->getName();
            }
        }

        if(empty($dropdown)){
            $player->sendMessage($plugin->getMessage("no-items"));
            return;
        }

        $form = new CustomForm(function(Player $player, ?array $data) use ($items, $inventory) : void{
            if($data === null){
                $player->sendMessage(Main::getInstance()->getMessage("form-cancelled"));
                return;
            }

            $slot = $items[$data[1]];

            Forms::sendRenameForm($player, $slot);
        });

        $form->setTitle($plugin->colorize($plugin->getMessages()->get("select-form-title")));

        $form->addLabel($plugin->colorize($plugin->getMessages()->get("select-form-message")));

        $form->addDropdown(
            $plugin->colorize($plugin->getMessages()->get("select-item-dropdown")),
            $dropdown
        );

        $form->addLabel($plugin->colorize($plugin->getMessages()->get("close-button-note")));

        $player->sendForm($form);
    }

    public static function sendRenameForm(Player $player, int $slot) : void{
        $plugin = Main::getInstance();

        $item = $player->getInventory()->getItem($slot);

        $form = new CustomForm(function(Player $player, ?array $data) use ($slot, $item) : void{
            if($data === null){
                $player->sendMessage(Main::getInstance()->getMessage("rename-cancelled"));
                return;
            }

            $name = str_replace("&", "§", $data[1]);

            Forms::sendConfirmForm($player, $slot, $name);
        });

        $form->setTitle($plugin->colorize($plugin->getMessages()->get("rename-form-title")));

        $form->addLabel(
            $plugin->getMessage(
                "rename-form-message",
                [
                    "item" => $item->getName()
                ]
            )
        );

        $form->addInput(
            $plugin->colorize($plugin->getMessages()->get("rename-input")),
            "&bMy Item"
        );

        $form->addLabel($plugin->colorize($plugin->getMessages()->get("close-button-note")));

        $player->sendForm($form);
    }

    public static function sendConfirmForm(Player $player, int $slot, string $name) : void{
        $plugin = Main::getInstance();

        $item = $player->getInventory()->getItem($slot);

        $cost = (int) $plugin->getSettings()->get("rename-cost-levels", 30);

        $bypass = $player->hasPermission("renamingui.bypass") || $player->isOp();

        $form = new SimpleForm(function(Player $player, ?int $data) use ($slot, $name, $cost, $bypass) : void{
            $plugin = Main::getInstance();

            if($data === null){
                $player->sendMessage($plugin->getMessage("rename-cancelled"));
                return;
            }

            if($data === 1){
                $player->sendMessage($plugin->getMessage("rename-cancelled"));
                return;
            }

            $item = $player->getInventory()->getItem($slot);

            if($item->isNull()){
                $player->sendMessage($plugin->getMessage("item-missing"));
                return;
            }

            if(!$bypass){
                if($player->getXpManager()->getXpLevel() < $cost){
                    $player->sendMessage(
                        $plugin->getMessage(
                            "not-enough-levels",
                            [
                                "cost" => $cost
                            ]
                        )
                    );
                    return;
                }

                $player->getXpManager()->subtractXpLevels($cost);
            }

            $item->setCustomName($name);

            $player->getInventory()->setItem($slot, $item);

            $player->sendMessage(
                $plugin->getMessage(
                    "rename-success",
                    [
                        "name" => $name,
                        "item" => $item->getName()
                    ]
                )
            );
        });

        $message = $bypass ?
            $plugin->getMessage(
                "confirm-message-free",
                [
                    "item" => $item->getName(),
                    "name" => $name
                ]
            )
            :
            $plugin->getMessage(
                "confirm-message",
                [
                    "item" => $item->getName(),
                    "name" => $name,
                    "cost" => $cost
                ]
            );

        $form->setTitle($plugin->colorize($plugin->getMessages()->get("confirm-form-title")));

        $form->setContent($message);

        $form->addButton($plugin->colorize($plugin->getMessages()->get("confirm-button")));
        $form->addButton($plugin->colorize($plugin->getMessages()->get("cancel-button")));

        $player->sendForm($form);
    }
}
