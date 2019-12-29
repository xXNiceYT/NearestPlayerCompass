<?php
namespace anirudh246\NearestPlayerCompass;

use pocketmine\scheduler\Task;
use pocketmine\item\Item;

class CompassTask extends Task{

    public function onRun(int $currentTick): void{
        foreach(Main::getInstance()->getServer()->getOnlinePlayers() as $player){
            if($player->getInventory()->getItemInHand()->getId() == Item::COMPASS){
                Main::getInstance()->sendCalculatedMessage($player);
            }
        }
    }
}
