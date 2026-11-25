<?php

namespace hiro\parts;

use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use React\Promise\Promise;

class Respondable
{
    /**
     * Message or Interaction object to respond.
     *
     * @var Message|Interaction
     */
    private Message|Interaction $respondable;

    /**
     * Channel object of the respondable object.
     *
     * @var Channel|null
     */
    public ?Channel $channel = null;

    /**
     * Member object of the respondable object.
     *
     * @var Member|null
     */
    public ?Member $member = null;

    /**
     * User object of the respondable object.
     *
     * @var User|null
     */
    public ?User $user = null;

    /**
     * Guild object of the respondable object.
     *
     * @var User|null
     */
    public ?User $author = null;

    /**
     * Guild object of the respondable object.
     *
     * @var Guild|null
     */
    public ?Guild $guild = null;

    /**
     * Collection of mentions in the respondable object.
     *
     * @var Collection
     */
    public Collection $mentions;

    /**
     * User ID of the respondable object.
     *
     * @var integer|null
     */
    public ?int $user_id = null;

    /**
     * Guild ID of the respondable object.
     *
     * @var integer|null
     */
    public ?int $guild_id = null;

    /**
     * Channel ID of the respondable object.
     *
     * @var integer|null
     */
    public ?int $channel_id = null;

    /**
     * Content of the respondable object. (Only for Message object)
     *
     * @var string|null
     */
    public ?string $content = null;

    public function __construct($respondable)
    {
        $this->respondable = $respondable;
        $this->channel ??= $respondable->channel;
        $this->member ??= $respondable->member;
        $this->user ??= $respondable->user;
        $this->author = $respondable->author ?? $respondable->user;
        $this->guild ??= $respondable->guild;
        $this->mentions = $respondable->mentions ?? new Collection();
        $this->content ??= $respondable->content;

        if (isset($this->author) && !isset($this->user)) $this->user = $this->author;
        if (isset($this->user) && !isset($this->author)) $this->author = $this->user;

        if (isset($respondable->user)) $this->user_id ??= $respondable->user->id;
        if (isset($respondable->guild)) $this->guild_id ??= $respondable->guild->id;
        if (isset($respondable->channel)) $this->channel_id ??= $respondable->channel->id;
    }

    /**
     * Reply method to reply to the message or interaction.
     *
     * @param string|Embed $message
     * @return Promise<Message>|null
     */
    public function reply(string|Embed $message): ?Promise
    {
        if ($this->respondable instanceof Interaction) {
            if ($message instanceof Embed) {
                return $this->respondable->respondWithMessage(MessageBuilder::new()->addEmbed($message));
            } else {
                return $this->respondable->respondWithMessage(MessageBuilder::new()->setContent($message));
            }
        } elseif ($this->respondable instanceof Message) {
            if($message instanceof Embed) {
                return $this->respondable->channel->sendMessage(MessageBuilder::new()->addEmbed($message));
            } else {
                return $this->respondable->channel->sendMessage($message);
            }
        } else {
            throw new \InvalidArgumentException('Invalid respondable object.');
        }
    }
}
