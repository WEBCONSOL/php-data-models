<?php

namespace WC\Models;

use \Throwable;

class Error
{
    /**
     * @var int $code
     */
    private $code = 0;
    /**
     * @var string $file
     */
    private $file = '';
    /**
     * @var string $line
     */
    private $line = '';
    /**
     * @var string $message
     */
    private $message = '';

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return is_string($this->code) ? (int)$this->code : $this->code;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getLine(): string
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @param string $line
     */
    public function setLine(string $line): void
    {
        $this->line = $line;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}