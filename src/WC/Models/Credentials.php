<?php

namespace WC\Models;

use WC\Utilities\StringUtil;

class Credentials {

    /**
     * @var string $username
     */
    private $username = '';
    /**
     * @var string $email
     */
    private $email = '';
    /**
     * @var string $password
     */
    private $password = '';
    /**
     * @var bool $usernameIsEmail
     */
    private $usernameIsEmail = false;

    public function __construct(array $credentials)
    {
        if (isset($credentials['username'])) {
            $this->username = (string)$credentials['username'];
            $this->email = $this->username;
        }
        if (!$this->email && isset($credentials['email'])) {
            $this->email = (string)$credentials['email'];
            if (!$this->username) {
                $this->username = $this->email;
                $this->usernameIsEmail = true;
            }
        }
        if (isset($credentials['password'])) {
            $this->password = (string)$credentials['password'];
        }
        if (StringUtil::isEmail($this->username)) {
            $this->usernameIsEmail = true;
        }
    }

    public function getUsername(): string {return $this->username;}
    public function getEmail(): string {return $this->email;}
    public function getPassword(): string {return $this->password;}
    public function isUsernameAnEmail(): bool {return $this->usernameIsEmail;}
}