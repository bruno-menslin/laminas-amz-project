<?php

namespace Local\Model;

class Type { // Module.php TypeTableGateway
    public $id; // propriedades que vao p index.phtml
    public $name;
    
    public function exchangeArray(array $data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
