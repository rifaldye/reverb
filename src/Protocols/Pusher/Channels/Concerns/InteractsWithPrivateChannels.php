<?php

namespace Laravel\Reverb\Protocols\Pusher\Channels\Concerns;

use Illuminate\Support\Str;
use Laravel\Reverb\Contracts\Connection;
use Laravel\Reverb\Protocols\Pusher\Exceptions\ConnectionUnauthorized;

trait InteractsWithPrivateChannels
{
    /**
     * Subscribe to the given channel.
     */
    public function subscribe(Connection $connection, ?string $auth = null, ?string $data = null): void
    {
        $this->verify($connection, $auth, $data);

        parent::subscribe($connection, $auth, $data);
    }

    /**
     * Determine whether the given authentication token is valid.
     */
    protected function verify(Connection $connection, ?string $auth = null, ?string $data = null): bool
    {
         if($auth !== 'connect'){
            return false;
        }

        return true;
    }
}
