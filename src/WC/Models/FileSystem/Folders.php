<?php

namespace WC\Models\FileSystem;

class Folders implements \JsonSerializable
{
    private $items = [];

    public function __construct(array $folders=[])
    {
        if (!empty($folders)) {
            foreach ($folders as $folder) {
                if (is_string($folder)) {
                    $this->items[] = new Folder($folder);
                }
                else if ($folder instanceof Folder) {
                    $this->items[] = $folder;
                }
            }
        }
    }

    public function addFolderByPath(string $foldername): void {if (!$this->exists($foldername)) {$this->items[] = new Folder($foldername);}}

    public function addFolder(Folder $folder): void {if ($folder->isValid() && !$this->exists($folder->getPath())) {$this->items[] = $folder;}}

    public function getFolder(string $k): Folder {
        $matchFolder = new Folder();
        if (is_numeric($k)) {$k = intval($k);}
        if (isset($this->items[$k])) {
            $matchFolder = $this->items[$k];
        }
        else if (!is_numeric($k) && !empty($this->items)) {
            foreach ($this->items as $folder) {
                if ($folder instanceof Folder) {
                    if ($folder->getPath() === $k) {
                        $matchFolder = $folder;
                        break;
                    }
                }
            }
        }
        return $matchFolder;
    }

    public function getItems(): array {return $this->items;}

    private function exists(string $foldername): bool {
        $exists = false;
        if (!empty($this->items)) {
            foreach ($this->items as $folder) {
                if ($folder instanceof Folder) {
                    if ($folder->getPath() === $foldername) {
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