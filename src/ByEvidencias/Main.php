<?php

namespace ByEvidencias;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\Config;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    private Config $config;
    private bool $invalidConfigReported = false;

    private const MAX_LEVEL = 255;
    private const MAX_DURATION = 1000000;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $this->setKillEffects($killer);
            }
        }
    }

    public function setKillEffects(Player $player): void {
        $effects = $this->config->get("effects", []);
        $message = $this->config->get("message", "");

        if (!$this->invalidConfigReported) {
            foreach ($effects as $effectData) {
                if (!isset($effectData["effect"]) || !isset($effectData["duration"]) || !isset($effectData["level"])) {
                    $player->sendMessage(TextFormat::RED . "Invalid effect configuration. Please check your config.");
                    $this->invalidConfigReported = true;
                    return;
                }
            }
        }

        foreach ($effects as $effectData) {
            $effectType = strtoupper($effectData["effect"]);
            $duration = (int)($effectData["duration"]) * 20;
            $level = (int)($effectData["level"]);

            if ($level > self::MAX_LEVEL) {
                $player->sendMessage(TextFormat::RED . "Maximum level reached in the effect " . TextFormat::YELLOW . $effectType . ". The limit is " . self::MAX_LEVEL);
                continue;
            }

            if ($duration > self::MAX_DURATION) {
                $player->sendMessage(TextFormat::RED . "Maximum duration reached in the effect " . TextFormat::YELLOW . $effectType . ". The limit is " . self::MAX_DURATION . " ticks (50000 seconds).");
                continue;
            }

            $effectClass = $this->getEffectByName($effectType);
            if ($effectClass !== null) {
                $effectInstance = new EffectInstance($effectClass, $duration, $level - 1);
                $player->getEffects()->add($effectInstance);
            } else {
                $this->getLogger()->warning("Unknown effect: " . $effectType);
            }
        }

        $message = str_replace("&", "§", $message);
        $player->sendMessage(TextFormat::colorize($message));
    }

    private function getEffectByName(string $name): ?\pocketmine\entity\effect\Effect {
        switch ($name) {
            case "SPEED":
                return VanillaEffects::SPEED();
            case "SLOW":
                return VanillaEffects::SLOWNESS();
            case "HASTE":
                return VanillaEffects::HASTE();
            case "MINING_FATIGUE":
                return VanillaEffects::MINING_FATIGUE();
            case "STRENGTH":
                return VanillaEffects::STRENGTH();
            case "JUMP_BOOST":
                return VanillaEffects::JUMP_BOOST();
            case "NAUSEA":
                return VanillaEffects::NAUSEA();
            case "REGENERATION":
                return VanillaEffects::REGENERATION();
            case "RESISTANCE":
                return VanillaEffects::RESISTANCE();
            case "FIRE_RESISTANCE":
                return VanillaEffects::FIRE_RESISTANCE();
            case "WATER_BREATHING":
                return VanillaEffects::WATER_BREATHING();
            case "INVISIBILITY":
                return VanillaEffects::INVISIBILITY();
            case "BLINDNESS":
                return VanillaEffects::BLINDNESS();
            case "NIGHT_VISION":
                return VanillaEffects::NIGHT_VISION();
            case "HUNGER":
                return VanillaEffects::HUNGER();
            case "WEAKNESS":
                return VanillaEffects::WEAKNESS();
            case "POISON":
                return VanillaEffects::POISON();
            case "WITHER":
                return VanillaEffects::WITHER();
            case "HEALTH_BOOST":
                return VanillaEffects::HEALTH_BOOST();
            case "ABSORPTION":
                return VanillaEffects::ABSORPTION();
            case "SATURATION":
                return VanillaEffects::SATURATION();
            case "LEVITATION":
                return VanillaEffects::LEVITATION();
            case "FATAL_POISON":
                return VanillaEffects::FATAL_POISON();
            case "CONDUIT_POWER":
                return VanillaEffects::CONDUIT_POWER();
            case "DOLPHINS_GRACE":
                return VanillaEffects::DOLPHINS_GRACE();
            default:
                return null;
        }
    }
}
