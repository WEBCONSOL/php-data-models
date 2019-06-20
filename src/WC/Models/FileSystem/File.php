<?php

namespace WC\Models\FileSystem;

use WC\Utilities\FileUtil;

class File implements \JsonSerializable
{
    /**
     * @var Folder $parent
     */
    private $parent;
    private $parentPath = '';
    private $path = '';
    private $name = '';
    private $title = '';
    private $description = '';
    private $author = '';
    private $copyright = '';
    private $modified_on = '';
    private $created_on = '';
    private $modified_by = '';
    private $created_by = '';
    /**
     * @var array $tags
     */
    private $tags = [];
    /**
     * @var FileAttributes $attrs
     */
    private $attrs;

    public function __construct(string $path='')
    {
        if (strlen($path) > 0) {
            $this->setPath($path);
            $this->setParentPath(dirname($path));
            $this->setName(pathinfo($path, PATHINFO_BASENAME));
            $this->setTitle(pathinfo($path, PATHINFO_FILENAME));
            $this->attrs = new FileAttributes();
            if (file_exists($path)) {
                $this->attrs->setRequiredName(pathinfo($path, PATHINFO_BASENAME));
                $this->attrs->setRequiredExtension(pathinfo($path, PATHINFO_EXTENSION));
                $this->attrs->setRequiredSize(filesize($path));
                $this->attrs->setRequiredMimeType(FileUtil::getMimetype($path));
                $imgAttrs = FileUtil::getImageAttributes($path);
                if (!empty($imgAttrs)) {
                    $this->attrs->setIsImage(true);
                    $this->attrs->setImageAttributes($imgAttrs);
                }
            }
        }
    }

    public function setParentPath(string $s): void {$this->parentPath = $s;}
    public function setParent(Folder $folder): void {$this->parent = $folder;}

    public function setPath(string $s): void {$this->path = $s;}
    public function setName(string $s): void {$this->name = $s;}
    public function setTitle(string $s): void {$this->title = $s;}
    public function setDescription(string $s): void {$this->description = $s;}
    public function setTags(array $a): void {$this->tags = $a;}
    public function setAuthor(string $s): void {$this->author = $s;}
    public function setCopyright(string $s): void {$this->copyright = $s;}
    public function setModifiedOn(string $s): void {$this->modified_on = $s;}
    public function setCreatedOn(string $s): void {$this->created_on = $s;}
    public function setModifiedBy(string $s): void {$this->modified_by = $s;}
    public function setCreatedBy(string $s): void {$this->created_by = $s;}
    public function setAttributes(FileAttributes $attrs): void {$this->attrs = $attrs;}

    public function getAttributes(): FileAttributes {$this->initAttrs();return $this->attrs;}
    public function getParent(): Folder {
        if (empty($this->parent)) {
            $this->parent = new Folder();
        }
        return $this->parent;
    }
    public function getParentPath(): string {return $this->parentPath;}

    public function getPath(): string {return $this->path;}
    public function getName(): string {return $this->name;}
    public function getTitle(): string {return $this->title;}
    public function getDescription(): string {return $this->description;}
    public function getTags(): array {return $this->tags;}
    public function getAuthor(): string {return $this->author;}
    public function getCopyright(): string {return $this->copyright;}
    public function getModifiedOn(): string {return ($this->modified_on?$this->modified_on:strtotime('now')).'';}
    public function getCreatedOn(): string {return ($this->created_on?$this->created_on:strtotime('now')).'';}
    public function getModifiedBy(): string {return $this->modified_by?$this->modified_by:'0';}
    public function getCreatedBy(): string {return $this->created_by?$this->created_by:'0';}

    public function isValid(): bool {return strlen($this->path) > 0;}

    public function jsonSerialize() {
        $this->initAttrs();
        return [
            'parent' => $this->parent,
            'parentPath' => $this->parentPath,
            'path' => $this->path,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'author' => $this->author,
            'copyright' => $this->copyright,
            'modified_on' => $this->getModifiedOn(),
            'created_on' => $this->getCreatedOn(),
            'modified_by' => $this->getModifiedBy(),
            'created_by' => $this->getCreatedBy(),
            'tags' => $this->tags,
            'attrs' => $this->attrs->jsonSerialize()
        ];
    }

    public function __toString(): string {return json_encode($this->jsonSerialize());}

    private function initAttrs() {if (empty($this->attrs)) {$this->attrs = new FileAttributes();}}
}