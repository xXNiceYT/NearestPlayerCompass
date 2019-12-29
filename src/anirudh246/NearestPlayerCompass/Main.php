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


class Main extends PluginBase implements Listener{
    
    private static $instance = null;
    
    public function onLoad(): void{
        self::$instance = $this;
    }
    
    public function onEnable(): void{
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new CompassTask(), 15);
    }
    
    public static function getInstance(): self{
        return self::$instance;
    }
    
    public function onItemHeld(PlayerItemHeldEvent $event){
        $player = $event->getPlayer();
        if($event->getItem()->getId() === ItemIds::COMPASS){
            $onlyOperator = (bool) $this->getConfig()->get('apply-only-permitted-player');
            if(($onlyOperator && $player->hasPermission('nearestplayercompass.allow.permission')) || $onlyOperator === false){
                $this->sendCalculatedMessage($player);
            }
        }
    }
 
    public function sendEachType(Player $player, string $message): void{
        switch(strtolower($this->getConfig()->get('sending-message-type'))){
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
    
    public function setSpawnPositionPacket(Player $player, Vector3 $pos): void{
        $pk = new SetSpawnPositionPacket();
        $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
        $pk->x = (int) $pos->getX();
        $pk->y = (int) $pos->getY();
        $pk->z = (int) $pos->getZ();
        $pk->spawnForced = false;
        $player->dataPacket($pk);
    }
    
    public function calculateNearestPlayer(Player $player): ?Player{
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
    
    public function sendCalculatedMessage(Player $player): void{
        $nearPlayer = $this->calculateNearestPlayer($player);
        $setNeedle = (bool) $this->getConfig()->get('set-needle-to-nearest');
        if($nearPlayer instanceof Player){
            $myVector = $player->asVector3();
            $nearVector = $nearPlayer->asVector3();
            $message = \str_replace(['@pn', '@dn', '@tn', '@d'] ,[$nearPlayer->getName(), $nearPlayer->getDisplayName(), $nearPlayer->getNameTag(), (int) $myVector->distance($nearVector)], $this->config->get('message-found-nearest-player'));
            $this->sendEachType($player, $message);
            if($setNeedle) $this->setSpawnPositionPacket($player, $nearVector);
        }else{
            $player->sendMessage($this->getConfig()->get('message-no-nearest-player'));
        }
    }
}
