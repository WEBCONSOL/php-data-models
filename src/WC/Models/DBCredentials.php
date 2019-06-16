<?php

namespace WC\Models;

class DBCredentials implements \JsonSerializable
{
    public $dsn;
    public $driver;
    public $host;
    public $port;
    public $charset;
    public $username;
    public $password;
    public $dbname;
    public $prefix;

    public function __construct(array $config)
    {
        if (isset($config['driver'])) {$this->driver=$config['driver'];}
        if (isset($config['host'])) {$this->host=$config['host'];}

        if (isset($config['port'])) {$this->port=$config['port'];}
        else {$this->port = '3306';}

        if (isset($config['charset'])) {$this->charset=$config['charset'];}
        else {$this->charset = 'UTF-8';}

        if (isset($config['user'])) {$this->username=$config['user'];}
        else if (isset($config['username'])) {$this->username=$config['username'];}
        else if (isset($config['u'])) {$this->username=$config['u'];}

        if (isset($config['password'])) {$this->password=$config['password'];}
        else if (isset($config['pwd'])) {$this->password=$config['pwd'];}
        else if (isset($config['pw'])) {$this->password=$config['pw'];}
        else if (isset($config['p'])) {$this->password=$config['p'];}

        if (isset($config['dbname'])) {$this->dbname=$config['dbname'];}
        else if (isset($config['database'])) {$this->dbname=$config['database'];}
        else if (isset($config['db'])) {$this->dbname=$config['db'];}
        else {$this->dbname = '';}

        if (isset($config['prefix'])) {$this->prefix=$config['prefix'];}
        else {$this->prefix = '';}

        if ($this->driver && $this->host && $this->port) {
            if ($this->driver === 'pdo_mysql') {$this->driver = 'mysql';}
            $this->dsn=$this->driver.':host='.$this->host.';port='.$this->port.($this->dbname?';dbname='.$this->dbname:'');
        }
    }

    public function isValid(): bool {return $this->dsn && $this->username && $this->password;}

    public function jsonSerialize() {return $this;}

    public function __toString():string {return json_encode($this->jsonSerialize());}
}