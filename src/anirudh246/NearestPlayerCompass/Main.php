<?php
namespace anirudh246\NearestPlayerCompass;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use anirudh246\NearestPlayerCompass\Main;


class Main extends PluginBase{
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder(), 0744, true);
        $this->saveResource('config.yml', false);
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        $this->getScheduler()->scheduleRepeatingTask(new CompassTask, 20);


    }

}


