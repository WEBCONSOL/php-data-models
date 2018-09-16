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
        return is_array($data) && in_array(self::CAN_READ, $data);
    }
    public function canModify($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_MODIFY, $data);
    }
    public function canCreate($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_CREATE, $data);
    }
    public function canDelete($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_DELETE, $data);
    }
    public function canReadACL($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_READ_ACL, $data);
    }
    public function canEditACL($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_EDIT_ACL, $data);
    }
    public function canReplicate($entity): bool {
        $data = $this->get($entity);
        return is_array($data) && in_array(self::CAN_REPLICATE, $data);
    }
}