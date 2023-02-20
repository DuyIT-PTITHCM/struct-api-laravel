<?php

namespace App\Libraries;

use IP2Proxy\Database;

// Ref : https://github.com/ip2location/ip2proxy-php
class IP2Proxy
{
    use Singleton;

    private $db = null;
    private string $cacheKey = 'IP2Proxy_DB';

    public function get($ip)
    {
        $this->load();
        return $this->db->lookup($ip, Database::ALL);
    }

    private function load()
    {
        if (empty($this->db)) {
            $filename = env('IP2PROXY_FILENAME', 'IP2PROXY.BIN');
            $pathProxyFile = database_path("ip2proxy/{$filename}");

            $this->db = new Database($pathProxyFile, env('IP2PROXY_MODE', Database::FILE_IO)); // Database::MEMORY_CACHE
        }
    }

    public function isProxy($ip)
    {
        $this->load();
        return $this->db->lookup($ip, Database::IS_PROXY);
    }

    public function getProxyType($ip)
    {
        $this->load();
        return $this->db->lookup($ip, Database::PROXY_TYPE);
    }
}
