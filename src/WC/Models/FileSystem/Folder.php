<?php

namespace WC\Models\FileSystem;

class Folder implements \JsonSerializable
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
     * @var Files $files
     */
    private $files;
    /**
     * @var Folders $folders
     */
    private $folders;

    public function __construct(string $path='')
    {
        if (strlen($path) > 0) {
            $this->setPath($path);
            $this->setParentPath(dirname($path));
            $this->setName(pathinfo($path, PATHINFO_BASENAME));
            $this->setTitle(pathinfo($path, PATHINFO_FILENAME));
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

    public function getFiles(): Files {$this->initFiles();return $this->files;}
    public function getFile(string $k): File {$this->initFiles();return $this->files->getFile($k);}
    public function addFileByPath(string $filename): void {$this->initFiles();$this->files->addFileByPath($filename);}
    public function addFile(File $file): void {$this->initFiles();$this->files->addFile($file);}

    public function getFolders(): Folders {$this->initFolders();return $this->folders;}
    public function getFolder(string $k): Folder {$this->initFolders();return $this->folders->getFolder($k);}
    public function addFolderByPath(string $foldername): void {$this->initFolders();$this->folders->addFolderByPath($foldername);}
    public function addFolder(Folder $folder): void {$this->initFolders();$this->folders->addFolder($folder);}

    public function isValid(): bool {return strlen($this->path) > 0;}

    public function jsonSerialize() {
        $this->initFiles();
        $this->initFolders();
        return [
            'parent' => $this->parent,
            'parentPath' => $this->parentPath,
            'path' => $this->path,
            'name' => $this->name,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'copyright' => $this->copyright,
            'modified_on' => $this->getModifiedOn(),
            'created_on' => $this->getCreatedOn(),
            'modified_by' => $this->getModifiedBy(),
            'created_by' => $this->getCreatedBy(),
            'tags' => $this->tags,
            'files' => $this->files->jsonSerialize(),
            'folders' => $this->folders->jsonSerialize()
        ];
    }

    public function __toString(): string {return json_encode($this->jsonSerialize());}

    private function initFiles() {if (empty($this->files)) {$this->files = new Files();}}
    private function initFolders() {if (empty($this->folders)) {$this->folders = new Folders();}}
}