<?php

namespace WC\Models;

final class UserProfileModel extends BaseModel
{
    protected $requiredFields = array('firstname', 'lastname', 'fullname', 'nickname', 'dob');
    public function getFirstName(): string {return $this->get('firstname', '');}
    public function getLastName(): string {return $this->get('lastname', '');}
    public function getFullName(): string {return $this->get('fullname', '');}
    public function getNickName(): string {return $this->get('nickname', '');}
    public function getDoB(): string {return $this->get('dob', '');}
}