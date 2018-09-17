<?php

namespace WC\Models;

class BaseModel implements \JsonSerializable
{
    protected $requiredFields = array();
    protected $data = array();
    public function __construct(array $data) {
        $this->data = $data;
        if (sizeof($this->requiredFields)) {
            $valid = true;
            foreach ($this->requiredFields as $field) {
                if (!$this->has($field)) {
                    $valid = false;
                    break;
                }
            }
            if (!$valid) {
                $this->data = array();
            }
        }
    }
    public final function get(string $k, $default=null) {return $this->has($k)?$this->data[$k]:$default;}
    public final function set(string $k, $v) {$this->data[$k]=$v;}
    public final function has(string $k): bool {return isset($this->data[$k]);}
    public final function is(string $k, $v): bool {return isset($this->data[$k]) && $this->data[$k] === $v;}
    public function delete(string $k) {if($this->has($k)){unset($this->data[$k]);}}
    public function reset() {$this->data = array();}
    public function getAsArray(): array {return $this->data;}
    public function isEmpty(): bool {return sizeof($this->data) <= 0;}
    public function isNotEmpty(): bool {return !$this->isEmpty();}
    public function mergeReplace(array $data) {
        if (sizeof($data)) {
            foreach ($data as $key=>$value) {
                $this->data[$key] = $value;
            }
        }
    }
    public function jsonSerialize() {return $this->data;}
}