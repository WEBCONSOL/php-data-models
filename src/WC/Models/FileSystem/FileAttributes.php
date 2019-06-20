<?php

namespace WC\Models\FileSystem;

class FileAttributes implements \JsonSerializable
{
    private $fields = ['name', 'mimetype', 'size', 'extension'];
    private $imageAttrs = ['width', 'height', 'is_image', 'bits', 'channels'];
    private $data = [];

    public function __construct(array $attrs=[])
    {
        if (!empty($attrs)) {
            $this->setRequiredAttributes($attrs);
            $this->setImageAttributes($attrs);
            if (!$this->isValid()) {throw new \Exception('Invalid attributes ('.FileAttributes::class.')');}
        }
    }

    public function setRequiredAttributes(array $attrs) {
        foreach ($this->fields as $k) {
            if (isset($attrs[$k])) {
                $this->data[$k] = $attrs[$k];
            }
        }
    }

    public function setImageAttributes(array $attrs) {
        foreach ($this->imageAttrs as $k) {
            if (isset($attrs[$k])) {
                $this->data[$k] = $attrs[$k];
            }
        }
    }

    public function setRequiredName(string $s): void {$this->data['name'] = $s;}
    public function setRequiredMimeType(string $s): void {$this->data['mimetype'] = $s;}
    public function setRequiredSize(string $s): void {$this->data['size'] = $s;}
    public function setRequiredExtension(string $s): void {$this->data['extension'] = $s;}

    public function setImageWidth(string $s): void {$this->data['width'] = $s;}
    public function setImageHeight(string $s): void {$this->data['height'] = $s;}
    public function setImageBits(string $s): void {$this->data['bits'] = $s;}
    public function setImageChannels(string $s): void {$this->data['channels'] = $s;}
    public function setIsImage(bool $b): void {$this->data['is_image'] = $b;}

    public function getName(): string {
        if (!isset($this->data['name'])) {
            throw new \Exception('Invalid attributes: name is missing ('.FileAttributes::class.')');
        }
        return $this->data['name'];
    }
    public function getMimeType(): string {
        if (!isset($this->data['mimetype'])) {
            throw new \Exception('Invalid attributes: mimetype is missing ('.FileAttributes::class.')');
        }
        return $this->data['mimetype'];
    }
    public function getSize(): string {
        if (!isset($this->data['size'])) {
            throw new \Exception('Invalid attributes: size is missing ('.FileAttributes::class.')');
        }
        return $this->data['size'];
    }
    public function getExtension(): string {
        if (!isset($this->data['extension'])) {
            throw new \Exception('Invalid attributes: extension is missing ('.FileAttributes::class.')');
        }
        return $this->data['extension'];
    }

    public function getWidth(): string {return isset($this->data['width']) ? $this->data['width'].'' : '';}
    public function getHeight(): string {return isset($this->data['height']) ? $this->data['height'].'' : '';}
    public function getImageBits(): string {return isset($this->data['bits']) ? $this->data['bits'].'' : '';}
    public function getImageChannels(): string {return isset($this->data['channels']) ? $this->data['channels'].'' : '';}
    public function getIsImage(): bool {return isset($this->data['is_image']) ? $this->data['is_image'] : false;}

    public function isValid(): bool {return sizeof($this->data) >= sizeof($this->fields);}

    public function jsonSerialize() {return $this->data;}

    public function __toString(): string {return json_encode($this->jsonSerialize());}
}