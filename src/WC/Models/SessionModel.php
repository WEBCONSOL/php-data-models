<?php

namespace WC\Models;

use WC\Utilities\EncodingUtil;

final class SessionModel extends BaseModel
{
    private static $content = null;
    public function getId(): string {return (int)($this->has('id') ? $this->get('id') : '');}
    public function getUserId(): int {return (int)($this->has('user_id') ? $this->get('user_id') : '0');}
    public function getCreateOn(): int {return (int)($this->has('created_on') ? $this->get('created_on') : '0');}
    public function getExpireOn(): int {return (int)($this->has('expired_on') ? $this->get('expired_on') : '0');}
    public function getData(): ListModel {
        if (self::$content === null) {
            self::$content = $this->has('content') ? $this->get('content') : [];
            if (!(self::$content instanceof ListModel)) {
                if (is_array(self::$content)) {self::$content = new ListModel(self::$content);}
                else if (EncodingUtil::isValidJSON(self::$content)) {self::$content = new ListModel(json_decode(self::$content, true));}
                else {self::$content = new ListModel([]);}
            }
        }
        return self::$content;
    }
    public function isValid(): bool {return $this->has('id') && $this->has('user_id') && $this->has('created_on') && $this->has('expired_on') && $this->has('content');}
}