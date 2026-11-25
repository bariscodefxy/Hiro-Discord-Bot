<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\consts\RPG;
use hiro\interfaces\GeneratorReturn;
use hiro\parts\generators\GithubImageGenerator;
use hiro\parts\generators\MonsterGenerator;
use hiro\parts\monster\Wolf;
use hiro\parts\rpg\AttackSystem;
use hiro\parts\rpg\LevelSystem;
use PHPUnit\Framework\TestCase;

final class RpgTest extends TestCase
{
    public function testRpgRacesArrayAndSwap(): void
    {
        $races = RPG::getRacesAsArray();
        $swapped = RPG::getRacesAsArray(true);

        $this->assertSame([
            'human' => RPG::HUMAN_RACE,
            'dragon' => RPG::DRAGON_RACE,
            'elf' => RPG::ELF_RACE,
            'evileyetribe' => RPG::EVILEYETRIBE_RACE,
            'oni' => RPG::ONI_RACE,
            'wolfhuman' => RPG::WOLFHUMAN_RACE,
        ], $races);

        $this->assertCount(count($races), array_unique($races));
        $this->assertSame('human', $swapped[RPG::HUMAN_RACE]);
        $this->assertSame('wolfhuman', $swapped[RPG::WOLFHUMAN_RACE]);
    }

    public function testLevelSystemRequiredExperiences(): void
    {
        $this->assertSame(0, LevelSystem::getRequiredExperiences(0));
        $this->assertSame(100, LevelSystem::getRequiredExperiences(1));
        $this->assertSame(500, LevelSystem::getRequiredExperiences(5));
        $this->assertSame(1000, LevelSystem::getRequiredExperiences(10));
    }

    public function testAttackSystemDamageWithinExpectedRange(): void
    {
        for ($level = 1; $level <= 10; $level++) {
            $min = $level * 1 + 5;
            $max = $level * 3 + 5;

            for ($i = 0; $i < 20; $i++) {
                $damage = AttackSystem::getAttackDamage($level);
                $this->assertGreaterThanOrEqual($min, $damage);
                $this->assertLessThanOrEqual($max, $damage);
            }
        }
    }

    public function testWolfMonsterDefaultsAndAccessors(): void
    {
        $wolf = new Wolf();

        $this->assertInstanceOf(GeneratorReturn::class, $wolf);
        $this->assertSame(15, $wolf->getHealth());
        $this->assertSame(5, $wolf->getDamage());
        $this->assertSame(5, $wolf->getXp());
        $this->assertSame('Demon Beast', $wolf->getType());
        $this->assertSame('Wolf', $wolf->getName());

        $wolf->setHealth(30);
        $wolf->setDamage(9);
        $wolf->setXp(12);
        $wolf->setType('Beast');
        $wolf->setImage('https://example.com/wolf.jpg');

        $this->assertSame(30, $wolf->getHealth());
        $this->assertSame(9, $wolf->getDamage());
        $this->assertSame(12, $wolf->getXp());
        $this->assertSame('Beast', $wolf->getType());
        $this->assertSame('https://example.com/wolf.jpg', $wolf->getImage());
    }

    public function testMonsterGeneratorGeneratesValidMonster(): void
    {
        $generator = new MonsterGenerator();
        $monster = $generator->generateRandom();

        $this->assertInstanceOf(GeneratorReturn::class, $monster);
        $this->assertInstanceOf(Wolf::class, $monster);
    }

    public function testGithubImageGeneratorBuildsExpectedUrls(): void
    {
        $this->assertSame(
            'https://raw.githubusercontent.com/bariscodefxy/Hiro-Discord-Bot/master/src/images/rpg/monsters/Wolf.jpg',
            GithubImageGenerator::generate('Wolf')
        );

        $this->assertSame(
            'https://raw.githubusercontent.com/bariscodefxy/Hiro-Discord-Bot/master/src/images/rpg/monsters/Dragon.png',
            GithubImageGenerator::generate('Dragon', '.png')
        );
    }
}
