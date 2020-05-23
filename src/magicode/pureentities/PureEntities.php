<?php

namespace magicode\pureentities;

use magicode\pureentities\entity\animal\walking\Chicken;
use magicode\pureentities\entity\animal\walking\Cow;
use magicode\pureentities\entity\animal\walking\Mooshroom;
use magicode\pureentities\entity\animal\walking\Ocelot;
use magicode\pureentities\entity\animal\walking\Pig;
use magicode\pureentities\entity\animal\walking\Rabbit;
use magicode\pureentities\entity\animal\walking\Sheep;
use magicode\pureentities\entity\monster\walking\Creeper;
use magicode\pureentities\entity\monster\walking\Wolf;
use pocketmine\block\Air;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\block\Block;

class PureEntities extends PluginBase implements Listener{

    public function onLoad(){
        $classes = [
            Chicken::class,
            Cow::class,
            Creeper::class,
            Mooshroom::class,
            Ocelot::class,
            Pig::class,
            Rabbit::class,
            Sheep::class,
            Wolf::class
        ];
        foreach($classes as $name){
            Entity::registerEntity($name);
            if(
                $name == IronGolem::class
                || $name == FireBall::class
                || $name == SnowGolem::class
                || $name == ZombieVillager::class
            ){
                continue;
            }
            $item = Item::get(Item::SPAWN_EGG, $name::NETWORK_ID);
            if(!Item::isCreativeItem($item)){
                Item::addCreativeItem($item);
            }
        }

        Tile::registerTile(Spawner::class);
        
        $this->getServer()->getLogger()->info(TextFormat::GOLD . "[PureEntitiesX]You're Running PureEntitiesX 2.0");
        
        $this->getServer()->getLogger()->info(TextFormat::GOLD . "[PureEntities]The Original Code for this Plugin was Written by milk0417. It is now being maintained by Magicode1 for PMMP.");
    }

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info(TextFormat::GOLD . "[PureEntities]Plugin has been enabled");
    }

    public function onDisable(){
        $this->getServer()->getLogger()->info(TextFormat::GOLD . "[PureEntities]Plugin has been disabled");
    }

    public static function create($type, Position $source, ...$args){
        $chunk = $source->getLevel()->getChunk($source->x >> 4, $source->z >> 4, true);
        if(!$chunk->isGenerated()){
            $chunk->setGenerated();
        }
        if(!$chunk->isPopulated()){
            $chunk->setPopulated();
        }

        $nbt = new CompoundTag("", [
            "Pos" => new ListTag("Pos", [
                new DoubleTag("", $source->x),
                new DoubleTag("", $source->y),
                new DoubleTag("", $source->z)
            ]),
            "Motion" => new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
            "Rotation" => new ListTag("Rotation", [
                new FloatTag("", $source instanceof Location ? $source->yaw : 0),
                new FloatTag("", $source instanceof Location ? $source->pitch : 0)
            ]),
        ]);
        return Entity::createEntity($type, $chunk, $nbt, ...$args);
    }

    public function PlayerInteractEvent(PlayerInteractEvent $ev){
        if($ev->getFace() == 255 || $ev->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $item = $ev->getItem();
        $block = $ev->getBlock();
        if($item->getId() === Item::SPAWN_EGG && $block->getId() == Item::MONSTER_SPAWNER){
            $ev->setCancelled();

            $tile = $block->level->getTile($block);
            if($tile != null && $tile instanceof Spawner){
                $tile->setSpawnEntityType($item->getDamage());
            }else{
                if($tile != null){
                    $tile->close();
                }
                $nbt = new CompoundTag("", [
                    new StringTag("id", Tile::MOB_SPAWNER),
                    new IntTag("EntityId", $item->getId()),
                    new IntTag("x", $block->x),
                    new IntTag("y", $block->y),
                    new IntTag("z", $block->z),
                ]);
                new Spawner($block->getLevel()->getChunk((int) $block->x >> 4, (int) $block->z >> 4), $nbt);
            }
        }
    }

    
    

    

}
