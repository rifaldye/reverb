<?php

namespace Laravel\Reverb\Protocols\Pusher\Channels\Concerns;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
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
        //validate auth is valid jwt token
        $key = $connection->app()->secret();
        if (!is_string($key)) {
            throw new ConnectionUnauthorized;
        }

        try{
            $decoded = JWT::decode($auth, new Key($key, 'HS256'));
        }catch(\Exception $e){
            throw new ConnectionUnauthorized;
        }

// [2025-04-30 17:18:47] staging.INFO: {"name":"john","access":["stockPrice"],"exp":1746004911}
        $channelName = explode('.', $this->name)[0];
        $channelName = explode('-', $channelName)[1];
        if(!in_array($channelName, $decoded->access)){
            throw new ConnectionUnauthorized;
        }
        return true;
    }
}
