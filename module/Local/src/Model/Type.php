<?php

namespace Local\Model;

class Type {
    public $typeId;
    public $typeName;
    
    public $id;
    public $name;
    public $type_id;
    
    public function exchangeArray(array $data)
    {
        $this->typeId = (!empty($data['TypeId'])) ? $data['TypeId'] : null;
        $this->typeName = (!empty($data['TypeName'])) ? $data['TypeName'] : null;
        
        $this->id = (!empty($data['LocalId'])) ? $data['LocalId'] : null;
        $this->name = (!empty($data['LocalName'])) ? $data['LocalName'] : null;
        $this->type_id = (!empty($data['type_id'])) ? $data['type_id'] : null;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
