<?php

namespace Laravel\Reverb\Protocols\Pusher\Channels\Concerns;

use Illuminate\Support\Str;
use Laravel\Reverb\Contracts\Connection;
use Laravel\Reverb\Protocols\Pusher\Exceptions\ConnectionUnauthorized;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
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

     protected function customVerify(Connection $connection, ?string $auth = null, ?string $data = null){
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

        $channelDecode = explode('.', $this->name);
        $channelName = explode('-', $channelDecode[0])[1];
        if(!in_array($channelName, $decoded->access)){
            throw new ConnectionUnauthorized;
        }
        // Dynamic channel validation - check if channel name property exists in JWT
        if (property_exists($decoded, $channelName) && $channelDecode[2] != $decoded->{$channelName}) {
            throw new ConnectionUnauthorized;
        }
        return true;
     }
    protected function verify(Connection $connection, ?string $auth = null, ?string $data = null): bool
    {
        $signature = "{$connection->id()}:{$this->name()}";

        if ($data) {
            $signature .= ":{$data}";
        }

        if (! hash_equals(
            hash_hmac(
                'sha256',
                $signature,
                $connection->app()->secret(),
            ),
            Str::after($auth, ':')
        )) {
            return $this->customVerify($connection, $auth, $data);
        }

        return true;
    }
}
