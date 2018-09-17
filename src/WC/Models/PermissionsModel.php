<?php

namespace WC\Models;

final class PermissionsModel extends BaseModel
{
    const CAN_READ = "action_read";
    const CAN_MODIFY = "action_modify";
    const CAN_CREATE = "action_create";
    const CAN_DELETE = "action_delete";
    const CAN_READ_ACL = "action_read_acl";
    const CAN_EDIT_ACL = "action_edit_acl";
    const CAN_REPLICATE = "action_replicate";

    public function add($entity, array $data) {$this->set($entity, $data);}

    public function canRead($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_READ && $value > 0) {
                return true;
            }
        }
        return false;
    }
    public function canModify($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_MODIFY && (int)$value === 1) {
                return true;
            }
        }
        return false;
    }
    public function canCreate($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_CREATE && $value > 0) {
                return true;
            }
        }
        return false;
    }
    public function canDelete($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_DELETE && $value > 0) {
                return true;
            }
        }
        return false;
    }
    public function canReadACL($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_READ_ACL && $value > 0) {
                return true;
            }
        }
        return false;
    }
    public function canEditACL($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_EDIT_ACL && $value > 0) {
                return true;
            }
        }
        return false;
    }
    public function canReplicate($entity): bool {
        $data = $this->get($entity);
        foreach ($data as $key=>$value) {
            if ($key === self::CAN_REPLICATE && $value > 0) {
                return true;
            }
        }
        return false;
    }
}