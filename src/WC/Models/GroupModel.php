<?php

namespace WC\Models;

final class GroupModel extends BaseModel
{
    protected $requiredFields = array('id', 'name', 'title', 'created_by', 'last_modified_by', 'created_datetime', 'last_modified_datetime');
    public function getId() {return $this->get('id');}
    public function getName(): string {return $this->get('username', '');}
    public function getTitle(): string {return $this->get('email', '');}
    public function getCreatedBy(): int {return (int)$this->get('created_by', '0');}
    public function getLastModifiedBy(): int {return (int)$this->get('last_modified_by', '0');}
    public function getCreatedDatetime(): int {return (int)$this->get('created_datetime', '0');}
    public function getLastModifiedDatetime(): int {return (int)$this->get('last_modified_datetime', '0');}
    public function getGroups(): GroupsModel {return $this->get('groups', new GroupsModel(array()));}
    public function getPermissions(): PermissionsModel {return $this->get('permissions', new PermissionsModel(array()));}
    public function isAMemberOf($idOrName): bool {
        $groups = $this->getGroups();
        if ($groups->isNotEmpty()) {
            $group = $groups->getGroup($idOrName);
            return $group->isNotEmpty();
        }
        return false;
    }
}