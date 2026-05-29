<?php

declare(strict_types=1);

namespace RenamingUI;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase{

    private static Main $instance;

    public static function getInstance() : Main{
        return self::$instance;
    }

    public function onEnable() : void{
        self::$instance = $this;

        @mkdir($this->getDataFolder());

        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");

        $this->getServer()->getCommandMap()->register("renameui", new RenameCommand());

        $this->getLogger()->info("RenamingUI Enabled");
    }

    public function getMessages() : Config{
        return new Config($this->getDataFolder() . "messages.yml", Config::YAML);
    }

    public function getSettings() : Config{
        return new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    public function colorize(string $text) : string{
        return str_replace("&", "§", $text);
    }

    public function getMessage(string $path, array $replace = []) : string{
        $message = $this->getMessages()->get($path, "Message Missing: " . $path);

        foreach($replace as $key => $value){
            $message = str_replace("{" . $key . "}", (string) $value, $message);
        }

        return $this->colorize($message);
    }
}
