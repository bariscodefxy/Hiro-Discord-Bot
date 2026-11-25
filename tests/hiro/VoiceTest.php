<?php

declare(strict_types=1);

namespace hiro\tests;

use Discord\Voice\VoiceClient;
use hiro\parts\voice\VoiceFile;
use hiro\parts\voice\VoiceSettings;
use PHPUnit\Framework\TestCase;

final class VoiceTest extends TestCase
{
    public function testVoiceFileDefaultsAndMutators(): void
    {
        $empty = new VoiceFile();
        $this->assertNull($empty->getTitle());
        $this->assertNull($empty->getUrl());
        $this->assertNull($empty->getAuthorId());

        $file = new VoiceFile('Song Title', 'https://example.com/watch?v=1', '123456');
        $this->assertSame('Song Title', $file->getTitle());
        $this->assertSame('https://example.com/watch?v=1', $file->getUrl());
        $this->assertSame('123456', $file->getAuthorId());

        $file->setTitle('Updated Title');
        $file->setUrl('https://example.com/watch?v=2');
        $file->setAuthorId('789012');

        $this->assertSame('Updated Title', $file->getTitle());
        $this->assertSame('https://example.com/watch?v=2', $file->getUrl());
        $this->assertSame('789012', $file->getAuthorId());
    }

    public function testVoiceSettingsQueueLoopAndNextSong(): void
    {
        $voiceClient = $this->createStub(VoiceClient::class);
        $settings = new VoiceSettings($voiceClient);

        $this->assertSame($voiceClient, $settings->getVoiceClient());
        $this->assertSame([], $settings->getQueue());
        $this->assertFalse($settings->getLoopEnabled());

        $first = new VoiceFile('First', 'https://example.com/1', '1');
        $second = new VoiceFile('Second', 'https://example.com/2', '2');

        $settings->addToQueue($first);
        $settings->addToQueue($second);
        $this->assertCount(2, $settings->getQueue());
        $this->assertSame($first, $settings->getQueue()[0]);

        $settings->setLoopEnabled(true);
        $this->assertTrue($settings->getLoopEnabled());

        $settings->nextSong();
        $this->assertCount(1, $settings->getQueue());
        $this->assertSame($second, $settings->getQueue()[0]);

        $settings->nextSong();
        $this->assertSame([], $settings->getQueue());

        // Calling nextSong on an empty queue should remain a safe no-op
        $settings->nextSong();
        $this->assertSame([], $settings->getQueue());
    }

    public function testVoiceSettingsCanReplaceQueueAndClearVoiceClient(): void
    {
        $voiceClient = $this->createStub(VoiceClient::class);
        $settings = new VoiceSettings($voiceClient);

        $replacement = [new VoiceFile('Only', 'https://example.com/only', '9')];
        $settings->setQueue($replacement);
        $this->assertSame($replacement, $settings->getQueue());

        $settings->setVoiceClient(null);
        $this->assertNull($settings->getVoiceClient());
    }
}
