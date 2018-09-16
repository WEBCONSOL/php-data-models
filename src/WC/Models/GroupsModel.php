<?php

namespace WC\Models;

final class GroupsModel extends BaseModel
{
    public function add(GroupModel $group) {$this->data[] = $group;}
    public function getGroup($idOrName): GroupModel {
        if (is_numeric($idOrName)) {
            return $this->getGroupById($idOrName);
        }
        return $this->getGroupByName($idOrName);
    }

    private function getGroupById($id): GroupModel {
        foreach ($this->data as $group) {
            if ($group instanceof GroupModel) {
                if ($id === $group->getId()) {
                    return $group;
                }
            }
        }
        return new GroupModel(array());
    }
    private function getGroupByName($name): GroupModel {
        foreach ($this->data as $group) {
            if ($group instanceof GroupModel) {
                if ($name === $group->getName()) {
                    return $group;
                }
            }
        }
        return new GroupModel(array());
    }
}