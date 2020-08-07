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
    /**
     * @var bool
     */
    private $isConnected = false;

    public function __construct(DBCredentials $config)
    {
        $this->config = $config;

        if ($this->config->isValid()) {
            try {
                $this->conn = new \PDO(
                    $config->dsn,
                    $config->username,
                    $config->password,
                    $config->options
                );
                if ($this->conn->errorCode()) {
                    throw new \RuntimeException(DBO::class.': error - '.json_encode($this->conn->errorInfo()), 500);
                }
                $this->isConnected = true;
            }
            catch (\Exception $e) {
                Logger::error($e);
            }
        }
        else {
            throw new \RuntimeException(DBO::class.': DBCredentials provided is invalid.', 500);
        }
    }

    /**
     * @return string
     */
    public function getPrefix(): string {return $this->conn ? $this->config->prefix : '';}

    /**
     * @param string $query
     * @param array  $params
     *
     * @return DBO
     */
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

    /**
     * @return string
     */
    public function lastInsertId() {
        if ($this->conn instanceof \PDO) {
            return $this->conn->lastInsertId();
        }
        else {
            throw new \RuntimeException(DBO::class.'.lastInsertId: conn is not an instance of PDO.', 500);
        }
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function loadAssoc(string $query=''): array {
        $result = [];
        if (!empty($query)) {
            $this->setQuery($query);
        }
        if ($this->stm instanceof \PDOStatement) {
            $query = $this->query($this->stm->queryString);
            if ($query instanceof \PDOStatement) {
                $result = $query->fetch(\PDO::FETCH_ASSOC);
                if (empty($result)) {
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

    /**
     * @param string $query
     *
     * @return array
     */
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

    /**
     * @param array $input_params
     *
     * @return bool
     */
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
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function quote(string $str): string {
        if ($this->conn instanceof \PDO) {
            return $this->conn->quote($str);
        }
        else {
            throw new \RuntimeException(DBO::class.'.quote: conn is not an instance of PDO.', 500);
        }
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function quoteName(string $str): string {return '`'.$str.'`';}

    /**
     * @param string $stm
     *
     * @return int
     */
    public function exec(string $stm): int {
        if ($this->conn instanceof \PDO) {
            try {
                $exec = $this->conn->query($stm);
                $errorCode = (int)($this->conn->errorCode()."");
                if ($errorCode > 0) {
                    $errorInfo = json_decode(json_encode($this->conn->errorInfo()), true);
                    throw new \RuntimeException(DBO::class.'.exec: '.end($errorInfo), 500);
                }
            }
            catch (\PDOException $e) {
                throw new \RuntimeException(DBO::class.'.exec: '.$e->getMessage(), 500);
            }
            return $exec ? 1 : 0;
        }
        else {
            throw new \RuntimeException(DBO::class.'.exec: conn is not an instance of PDO.', 500);
        }
    }

    /**
     * @param string $stm
     *
     * @return false|\PDOStatement
     */
    public function query(string $stm) {
        if ($this->conn instanceof \PDO) {
            $query = $this->conn->query($stm, \PDO::FETCH_ASSOC);
            $errorCode = (int)($this->conn->errorCode()."");
            if ($errorCode > 0) {
                $errorInfo = json_decode(json_encode($this->conn->errorInfo()), true);
                throw new \RuntimeException(DBO::class.'.exec: '.end($errorInfo), 500);
            }
            return $query;
        }
        else {
            throw new \RuntimeException(DBO::class.'.query: conn is not an instance of PDO.', 500);
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool {return $this->isConnected;}

    /**
     * Close the db connection
     */
    public function closeConnection(): void {
        $this->config = null;
        $this->stm = null;
        $this->conn = null;
        $this->isConnected = false;
    }

    /**
     * @param string $tableName
     *
     * @return TableColumns
     */
    public final function getTableColumns(string $tableName): TableColumns {
        $query = 'DESCRIBE ' . $tableName;
        return (new TableColumns($this->loadAssocList($query)));
    }

    /**
     * @param string $dbName
     *
     * @return bool
     */
    public final function dbExists(string $dbName): bool {
        $dbExistStm = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$dbName."'";
        $row = null;
        $row = $this->loadAssoc($dbExistStm);
        return !empty($row) && is_array($row) && isset($row['SCHEMA_NAME']);
    }

    public function jsonSerialize() {return $this->config->jsonSerialize();}

    public function __toString() {return json_encode($this->jsonSerialize());}
}