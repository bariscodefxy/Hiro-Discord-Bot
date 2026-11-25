<?php

declare(strict_types=1);

namespace hiro\tests;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use Discord\Voice\VoiceClient;
use hiro\Hiro;
use hiro\parts\CommandLoader;
use hiro\parts\Respondable;
use hiro\parts\voice\VoiceSettings;
use hiro\security\AuthorCommand;
use hiro\security\MusicCommand;
use PHPUnit\Framework\TestCase;

final class SecurityCommandsTest extends TestCase
{
    private ?string $originalAuthorEnv = null;
    private bool $hadAuthorEnv = false;
    private mixed $originalVoiceSettings = null;
    private bool $hadVoiceSettings = false;

    protected function setUp(): void
    {
        $this->hadAuthorEnv = array_key_exists('AUTHOR', $_ENV);
        $this->originalAuthorEnv = $this->hadAuthorEnv ? (string) $_ENV['AUTHOR'] : null;

        $this->hadVoiceSettings = array_key_exists('voiceSettings', $GLOBALS);
        $this->originalVoiceSettings = $this->hadVoiceSettings ? $GLOBALS['voiceSettings'] : null;
    }

    protected function tearDown(): void
    {
        if ($this->hadAuthorEnv) {
            $_ENV['AUTHOR'] = $this->originalAuthorEnv;
        } else {
            unset($_ENV['AUTHOR']);
        }

        if ($this->hadVoiceSettings) {
            $GLOBALS['voiceSettings'] = $this->originalVoiceSettings;
        } else {
            unset($GLOBALS['voiceSettings']);
        }
    }

    public function testAuthorCommandRejectsMissingRespondable(): void
    {
        $cmd = new AuthorCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $this->assertFalse($cmd->securityChecks([]));
    }

    public function testAuthorCommandRejectsNonAuthorAndAcceptsAuthor(): void
    {
        $_ENV['AUTHOR'] = '999888';
        $cmd = new AuthorCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $nonAuthor = $this->createMock(Respondable::class);
        $nonAuthor->user = $this->makeUser('111222');
        $nonAuthor->expects($this->once())
            ->method('reply')
            ->with('Only bot author can use this command.');

        $this->assertFalse($cmd->securityChecks(['respondable' => $nonAuthor]));

        $author = $this->createMock(Respondable::class);
        $author->user = $this->makeUser('999888');
        $author->expects($this->never())->method('reply');

        $this->assertTrue($cmd->securityChecks(['respondable' => $author]));
    }

    public function testMusicCommandRejectsMissingRespondable(): void
    {
        $cmd = new MusicCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $this->assertFalse($cmd->securityChecks([]));
    }

    public function testMusicCommandRejectsUserNotInVoiceChannel(): void
    {
        $cmd = new MusicCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $member = $this->createStub(Member::class);
        $member->method('getVoiceChannel')->willReturn(null);

        $respondable = $this->createMock(Respondable::class);
        $respondable->member = $member;
        $respondable->expects($this->once())
            ->method('reply')
            ->with("You're must be in a voice channel.");

        $this->assertFalse($cmd->securityChecks([
            'respondable' => $respondable,
            'client' => $this->createStub(Hiro::class),
        ]));
    }

    public function testMusicCommandRejectsWhenBotNotInVoiceChannel(): void
    {
        $cmd = new MusicCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $userChannel = $this->makeChannel('42');
        $member = $this->createStub(Member::class);
        $member->method('getVoiceChannel')->willReturn($userChannel);

        $client = $this->createStub(Hiro::class);
        $client->method('getVoiceClient')->willReturn(null);

        $respondable = $this->createMock(Respondable::class);
        $respondable->member = $member;
        $respondable->guild_id = 10;
        $respondable->expects($this->once())
            ->method('reply')
            ->with('You should use join command first.');

        $this->assertFalse($cmd->securityChecks([
            'respondable' => $respondable,
            'client' => $client,
        ]));
    }

    public function testMusicCommandRejectsDifferentVoiceChannel(): void
    {
        $cmd = new MusicCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $userChannel = $this->makeChannel('42');
        $botChannel = $this->makeChannel('99');

        $member = $this->createStub(Member::class);
        $member->method('getVoiceChannel')->willReturn($userChannel);

        $voiceClient = $this->createStub(VoiceClient::class);
        $voiceClient->channel = $botChannel;

        $client = $this->createStub(Hiro::class);
        $client->method('getVoiceClient')->willReturn($voiceClient);

        $respondable = $this->createMock(Respondable::class);
        $respondable->member = $member;
        $respondable->guild_id = 10;
        $respondable->expects($this->once())
            ->method('reply')
            ->with('You must be in same channel with me.');

        $this->assertFalse($cmd->securityChecks([
            'respondable' => $respondable,
            'client' => $client,
        ]));
    }

    public function testMusicCommandRejectsMissingVoiceSettingsAndAcceptsValidState(): void
    {
        $cmd = new MusicCommand($this->createStub(Hiro::class), $this->createStub(CommandLoader::class));

        $sharedChannel = $this->makeChannel('42');
        $member = $this->createStub(Member::class);
        $member->method('getVoiceChannel')->willReturn($sharedChannel);

        $voiceClient = $this->createStub(VoiceClient::class);
        $voiceClient->channel = $sharedChannel;

        $client = $this->createStub(Hiro::class);
        $client->method('getVoiceClient')->willReturn($voiceClient);

        $GLOBALS['voiceSettings'] = [];

        $missingSettingsResp = $this->createMock(Respondable::class);
        $missingSettingsResp->member = $member;
        $missingSettingsResp->guild_id = 10;
        $missingSettingsResp->expects($this->once())
            ->method('reply')
            ->with("Voice options couldn't found.");

        $this->assertFalse($cmd->securityChecks([
            'respondable' => $missingSettingsResp,
            'client' => $client,
        ]));

        $GLOBALS['voiceSettings'][10] = new VoiceSettings($voiceClient);

        $validResp = $this->createMock(Respondable::class);
        $validResp->member = $member;
        $validResp->guild_id = 10;
        $validResp->expects($this->never())->method('reply');

        $this->assertTrue($cmd->securityChecks([
            'respondable' => $validResp,
            'client' => $client,
        ]));
    }

    private function makeUser(string $id): User
    {
        $discord = $this->createStub(Discord::class);
        return new User($discord, ['id' => $id]);
    }

    private function makeChannel(string $id): Channel
    {
        $discord = $this->createStub(Discord::class);
        return new Channel($discord, ['id' => $id]);
    }
}
