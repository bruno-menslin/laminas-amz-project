<?php

namespace Local\Model;

class Type { // Module.php TypeTableGateway
    public $typeId; // propriedades que vao p index.phtml
    public $typeName;
    
    public $localId;
    public $localName;
    public $type_id;
    
    public function exchangeArray(array $data)
    {
        $this->typeId = (!empty($data['typeId'])) ? $data['typeId'] : null;
        $this->typeName = (!empty($data['typeName'])) ? $data['typeName'] : null;
        
        $this->localId = (!empty($data['localId'])) ? $data['localId'] : null;
        $this->localName = (!empty($data['localName'])) ? $data['localName'] : null;
        $this->type_id = (!empty($data['type_id'])) ? $data['type_id'] : null;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
