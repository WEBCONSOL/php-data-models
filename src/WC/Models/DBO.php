<?php

namespace WC\Models;

use WC\Utilities\Logger;

class DBO implements \JsonSerializable
{
    /**
     * @var \PDO $conn
     */
    private $conn;
    /**
     * @var DBCredentials $config
     */
    private $config;
    /**
     * @var \PDOStatement $stm
     */
    private $stm;

    public function __construct(DBCredentials $config)
    {
        $this->config = $config;

        if ($this->config->isValid()) {
            try {
                $this->conn = new \PDO($config->dsn, $config->username, $config->password);
                if ($this->conn->errorCode()) {
                    throw new \RuntimeException(DBO::class.': error - '.json_encode($this->conn->errorInfo()), 500);
                }
            }
            catch (\Exception $e) {
                Logger::error($e);
            }
        }
        else {
            throw new \RuntimeException(DBO::class.': DBCredentials provided is invalid.', 500);
        }
    }

    public function getPrefix(): string {return $this->conn ? $this->config->prefix : '';}

    public function setQuery(string $query, array $params=[]): DBO {
        if ($this->conn instanceof \PDO) {
            $this->stm = $this->conn->prepare($query);
            if (!empty($params)) {
                foreach ($params as $k=>$v) {
                    if (!is_numeric($k) && strpos($query, ':'.$k) !== false) {
                        $this->stm->bindParam(':'.$k, $v);
                    }
                }
            }
        }
        else {
            throw new \RuntimeException(DBO::class.'.setQuery: conn is not an instance of PDO.', 500);
        }
        return $this;
    }

    public function lastInsertId() {
        if ($this->conn instanceof \PDO) {
            return $this->conn->lastInsertId();
        }
        else {
            throw new \RuntimeException(DBO::class.'.lastInsertId: conn is not an instance of PDO.', 500);
        }
    }

    public function loadAssoc(string $query=''): array {
        $result = [];
        if (!empty($query)) {
            $this->setQuery($query);
        }
        if ($this->stm instanceof \PDOStatement) {
            $query = $this->query($this->stm->queryString);
            if ($query instanceof \PDOStatement) {
                $result = $query->fetch(\PDO::FETCH_ASSOC);
                if (!is_array($result)) {
                    $result = [];
                }
            }
            $this->stm = null;
        }
        else {
            throw new \RuntimeException(DBO::class.'.loadAssoc: stm is not instance of PDOStatement', 500);
        }
        return $result;
    }

    public function loadAssocList(string $query=''): array {
        $result = [];
        if (!empty($query)) {
            $this->setQuery($query);
        }
        if ($this->stm instanceof \PDOStatement) {
            $query = $this->query($this->stm->queryString);
            if ($query instanceof \PDOStatement) {
                $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                if (!is_array($result)) {
                    $result = [];
                }
            }
            $this->stm = null;
        }
        else {
            throw new \RuntimeException(DBO::class.'.loadAssocList: stm is not instance of PDOStatement', 500);
        }
        return $result;
    }

    public function execute(array $input_params=[]): bool {
        if ($this->stm instanceof \PDOStatement) {
            if (!empty($input_params)) {
                $result = $this->stm->execute($input_params);
                $this->stm = null;
                return is_bool($result) ? $result : false;
            }
            else {
                $result = $this->stm->execute();
                $this->stm = null;
                return is_bool($result) ? $result : false;
            }
        }
        else {
            throw new \RuntimeException(DBO::class.': stm is not instance of PDOStatement', 500);
        }
        return false;
    }

    public function quote(string $str): string {
        if ($this->conn instanceof \PDO) {
            return $this->conn->quote($str);
        }
        else {
            throw new \RuntimeException(DBO::class.'.quote: conn is not an instance of PDO.', 500);
        }
    }

    public function quoteName(string $str): string {return '`'.$str.'`';}

    public function exec(string $stm) {
        if ($this->conn instanceof \PDO) {
            return $this->conn->exec($stm);
        }
        else {
            throw new \RuntimeException(DBO::class.'.exec: conn is not an instance of PDO.', 500);
        }
    }

    public function query(string $stm) {
        if ($this->conn instanceof \PDO) {
            return $this->conn->query($stm, \PDO::FETCH_ASSOC);
        }
        else {
            throw new \RuntimeException(DBO::class.'.query: conn is not an instance of PDO.', 500);
        }
    }

    public function jsonSerialize() {return $this->config->jsonSerialize();}

    public function __toString() {return json_encode($this->jsonSerialize());}
}