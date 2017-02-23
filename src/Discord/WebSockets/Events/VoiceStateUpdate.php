<?php

/*
 * This file is apart of the DiscordPHP project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord\WebSockets\Events;

use Discord\Parts\WebSockets\VoiceStateUpdate as VoiceStateUpdatePart;
use Discord\WebSockets\Event;
use React\Promise\Deferred;

class VoiceStateUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function handle(Deferred $deferred, $data)
    {
        $state = $this->factory->create(VoiceStateUpdatePart::class, $data, true);

        if ($this->discord->options['storeVoiceMembers']) {
            foreach ($this->discord->guilds as $guild) {
                if ($guild->id == $state->guild_id) {
                    foreach ($guild->channels as $channel) {
                        if ($channel->members->has($state->user_id)) {
                            $channel->members->pull($state->user_id);
                        }

                        if ($channel->id == $state->channel_id) {
                            $channel->members->offsetSet($state->user_id, $state);
                        }
                    }
                } else {
                    if ($this->discord->users->has($state->user_id)) {
                        $user = $this->discord->users->offsetGet($state->user_id);
                        if (! $user->bot) {
                            foreach ($guild->channels as $channel) {
                                if ($channel->members->has($state->user_id)) {
                                    $channel->members->pull($state->user_id);
                                }
                            }
                        }
                    }
                }
            }
        }

        $deferred->resolve($state);
    }
}
