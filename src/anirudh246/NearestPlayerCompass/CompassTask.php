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
use anirudh246\NearestPlayerCompass\Main

class CompassTask extends Task implements Listener {
    public function onRun(int $currentTick) : void{
        public function onItemHeld(PlayerItemHeldEvent $event){
            $player = $event->getPlayer();
            if($event->getItem()->getId() === ItemIds::COMPASS){
                $onlyOperator = (bool) $this->config->get('apply-only-permitted-player');
                if(($onlyOperator && $player->hasPermission('nearestplayercompass.allow.permission')) || $onlyOperator === false){
                    $setNeedle = (bool) $this->config->get('set-needle-to-nearest');
                    $nearPlayer = $this->calculateNearestPlayer($player);
                    if($nearPlayer instanceof Player){
                        $myVector = $player->asVector3();
                        $nearVector = $nearPlayer->asVector3();
                        $message = \str_replace(['@pn', '@dn', '@tn', '@d'] ,[$nearPlayer->getName(), $nearPlayer->getDisplayName(), $nearPlayer->getNameTag(), (int) $myVector->distance($nearVector)], $this->config->get('message-found-nearest-player'));
                        $this->sendEachType($player, $message);
                        if($setNeedle) $this->setSpawnPositionPacket($player, $nearVector);
                    }else{
                        $player->sendMessage($this->config->get('message-no-nearest-player'));
                    }
                }
            }
        }

        private function sendEachType(Player $player, string $message){
            switch(strtolower($this->config->get('sending-message-type'))){
                case 'tip':
                    $player->sendTip($message);
                    break;

                case 'popup':
                    $player->sendPopup($message);
                    break;

                default:
                    $player->sendMessage($message);
                    break;
            }
        }

        private function setSpawnPositionPacket(Player $player, Vector3 $pos) : void{
            $pk = new SetSpawnPositionPacket();
            $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
            $pk->x = (int) $pos->getX();
            $pk->y = (int) $pos->getY();
            $pk->z = (int) $pos->getZ();
            $pk->spawnForced = false;
            $player->dataPacket($pk);
            $time = 0.1;
            $this->getServer()->getScheduler()->scheduleRepeatingTask($pk, $time);
        }


        private function calculateNearestPlayer(Player $player) : ?Player{
            $closest = null;
            if($player instanceof Position){
                $lastSquare = -1;
                $onLevelPlayer = $player->getLevel()->getPlayers();
                unset($onLevelPlayer[array_search($player, $onLevelPlayer)]);
                foreach($onLevelPlayer as $p){
                    $square = $player->distanceSquared($p);
                    if($lastSquare === -1 || $lastSquare > $square){
                        $closest = $p;
                        $lastSquare = $square;
                    }
                }
            }
            return $closest;
        }

    }
}
        ,
        20); //period/interval



    }


    }
}
