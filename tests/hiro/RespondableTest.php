<?php

declare(strict_types=1);

namespace hiro\tests;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use hiro\parts\Respondable;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

final class RespondableTest extends TestCase
{
    public function testWrapsMessageAndRepliesThroughChannel(): void
    {
        $discord = $this->createStub(Discord::class);
        $sent = [];

        $channel = new class($discord, ['id' => '3003']) extends Channel {
            public array $sentRef = [];

            public function sendMessage($message, bool $tts = false, $embed = null, $allowed_mentions = null, ?Message $replyTo = null): PromiseInterface
            {
                $this->sentRef[] = $message;
                return new Promise(function () {});
            }
        };

        $author = new User($discord, ['id' => '1001']);
        $guild = new Guild($discord, ['id' => '2002']);
        $member = new Member($discord, []);
        $mentions = new Collection();

        $message = new class($discord, ['content' => '!ping']) extends Message {
            public $channel;
            public $member;
            public $user;
            public $author;
            public $guild;
            public $mentions;
            public $content = '!ping';
        };
        $message->channel = $channel;
        $message->author = $author;
        $message->guild = $guild;
        $message->member = $member;
        $message->mentions = $mentions;

        $respondable = new Respondable($message);

        $this->assertSame($author, $respondable->author);
        $this->assertSame($author, $respondable->user);
        $this->assertSame(2002, $respondable->guild_id);
        $this->assertSame(3003, $respondable->channel_id);
        $this->assertSame('!ping', $respondable->content);
        $this->assertSame($mentions, $respondable->mentions);

        $promise1 = $respondable->reply('Pong!');
        $embed = $this->createStub(Embed::class);
        $promise2 = $respondable->reply($embed);

        $this->assertInstanceOf(Promise::class, $promise1);
        $this->assertInstanceOf(Promise::class, $promise2);
        $this->assertCount(2, $channel->sentRef);
        $this->assertSame('Pong!', $channel->sentRef[0]);
        $this->assertInstanceOf(MessageBuilder::class, $channel->sentRef[1]);
    }

    public function testWrapsInteractionAndRepliesWithRespondWithMessage(): void
    {
        $discord = $this->createStub(Discord::class);
        $user = new User($discord, ['id' => '5005']);
        $guild = new Guild($discord, ['id' => '6006']);
        $channel = new Channel($discord, ['id' => '7007']);

        $interaction = new class($discord) extends Interaction {
            public $channel;
            public $member;
            public $user;
            public $author;
            public $guild;
            public $mentions;
            public $content;
            public array $responses = [];

            public function respondWithMessage(MessageBuilder|string $builder, bool $ephemeral = false): PromiseInterface
            {
                $this->responses[] = $builder;
                return new Promise(function () {});
            }
        };
        $interaction->user = $user;
        $interaction->guild = $guild;
        $interaction->channel = $channel;

        $respondable = new Respondable($interaction);

        $this->assertSame($user, $respondable->user);
        $this->assertSame($user, $respondable->author);
        $this->assertSame(5005, $respondable->user_id);
        $this->assertSame(6006, $respondable->guild_id);
        $this->assertSame(7007, $respondable->channel_id);

        $respondable->reply('Hello slash');
        $respondable->reply($this->createStub(Embed::class));

        $this->assertCount(2, $interaction->responses);
        $this->assertInstanceOf(MessageBuilder::class, $interaction->responses[0]);
        $this->assertInstanceOf(MessageBuilder::class, $interaction->responses[1]);
    }

    public function testConstructorRejectsInvalidRespondableObject(): void
    {
        $invalid = new \stdClass();
        $invalid->channel = null;
        $invalid->member = null;
        $invalid->user = null;
        $invalid->author = null;
        $invalid->guild = null;
        $invalid->mentions = null;
        $invalid->content = null;

        $this->expectException(\TypeError::class);
        new Respondable($invalid);
    }
}
