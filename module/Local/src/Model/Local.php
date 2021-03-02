<?php

namespace Local\Model;

class Local {
    public $id;
    public $name;
    public $type_id;
    
    public function exchangeArray(array $data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;
        $this->type_id = (!empty($data['type_id'])) ? $data['type_id'] : null;
    }
}
