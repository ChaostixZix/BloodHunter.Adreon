<?php

namespace Chaostix;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use onebone\economyapi\economyapi;
use pocketmine\utils\TextFormat;

class main extends PluginBase implements Listener
{

    private $drops;
    private $xp;
    private $conf;
    public function onEnable()
    {
        $this->saveConfig();
        $this->conf = $this->getConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function PlayerDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $this->drops[$player->getName()][1] = $player->getInventory()->getArmorContents();
        $this->drops[$player->getName()][0] = $player->getInventory()->getContents();
        $this->xp[$player->getName()][0] = $player->getXpLevel();
        $world = $this->conf->get('world');
        if(strtolower($player->getLevel()->getName()) == strtolower($world))
        {
            $item = Item::get(331);
            $item->setCustomName($player->getName() . "'s Blood");
            $duidnow = economyapi::getInstance()->myMoney($player);
            $duid = $duidnow * 0.02;
            $xpnow = $player->getXpLevel();
            $xp = $xpnow * 0.1;
            if($xp == 0)
            {
                $xp = 10;
            }
            $item->setLore(array("Blood", (string)$duid, (string)$xp));
            economyapi::getInstance()->setMoney($player, $duidnow - $duid);
            $event->setDrops(array($item));
            $player->sendMessage(TextFormat::BOLD . TextFormat::AQUA . "Kamu Dibunuh.. Uangmu Berkurang " . $duid . TextFormat::BLUE . "K" . TextFormat::GOLD . "Coin");
        }else{
            $event->setDrops(array());
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if($sender instanceof Player)
        {
            $this->Claim($sender);
            return;
        }
        $sender->sendMessage("Ingame Command Only!!");
    }

    private function Claim(Player $player)
    {
        $itemhand = $player->getInventory()->getItemInHand();
        if($itemhand->getId() == 331 and $itemhand->hasCustomName())
        {
            if(isset($itemhand->getLore()[0]) and $itemhand->getLore()[0] == "Blood")
            {
                $player->getInventory()->removeItem($itemhand);
                economyapi::getInstance()->setMoney($player, economyapi::getInstance()->myMoney($player) + (int)$itemhand->getLore()[1]);
                $player->sendMessage(TextFormat::BOLD . TextFormat::AQUA . "Kamu Mendapat.. " . $itemhand->getLore()[1] . TextFormat::BLUE . "K" . TextFormat::GOLD . "Coin | " .TextFormat::AQUA.$itemhand->getLore()[2]." Level Ditambahkan");
                $xpnow = $player->getXpLevel();
                $xptot = (int)$itemhand->getLore()[2];
                $xp = $xpnow + $xptot;
                var_dump($xp);
                $player->setXpLevel($xp);
                var_dump($player->getXpLevel());
            }else{
                $player->sendMessage(TextFormat::GOLD . "Item yang kamu pegang bukan blood :)");
            }
        }else{
            $player->sendMessage(TextFormat::RED . "Tolong bloodnya dipegang..");
        }
    }
}