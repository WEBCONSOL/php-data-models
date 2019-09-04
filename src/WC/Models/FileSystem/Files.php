<?php

namespace WC\Models\FileSystem;

class Files implements \JsonSerializable
{
    private $items = [];

    public function __construct(array $files=[])
    {
        if (!empty($files)) {
            foreach ($files as $file) {
                if (is_string($file)) {
                    $this->items[] = new File($file);
                }
                else if ($file instanceof File) {
                    $this->items[] = $file;
                }
            }
        }
    }

    public function addFileByPath(string $filename): void {if (!$this->exists($filename)) {$this->items[] = new File($filename);}}

    public function addFile(File $file): void {
        if (!$this->exists($file->getPath())) {$this->items[] = $file;}
    }

    public function getFile(string $k): File {
        $matchFile = new File();
        if (is_numeric($k)) {$k = intval($k);}
        if (isset($this->items[$k])) {
            $matchFile = $this->items[$k];
        }
        else if (!is_numeric($k) && !empty($this->items)) {
            foreach ($this->items as $file) {
                if ($file instanceof File) {
                    if ($file->getPath() === $k) {
                        $matchFile = $file;
                        break;
                    }
                }
            }
        }
        return $matchFile;
    }

    public function getItems(): array {return $this->items;}

    private function exists(string $filename): bool {
        $exists = false;
        if (!empty($this->items)) {
            foreach ($this->items as $file) {
                if ($file instanceof File) {
                    if ($file->getPath() === $filename) {
                        $exists = true;
                        break;
                    }
                }
            }
        }
        return $exists;
    }

    public function jsonSerialize() {return $this->getItems();}

    public function __toString(): string {return json_encode($this->jsonSerialize());}

    public function isEmpty(): bool {return empty($this->items);}
}