<?php

namespace Local\Form;

use Laminas\Form\Form;

class LocalForm extends Form {
    
    public $localTypes = ['Local type'];
    
    public function __construct($types)
    {
        parent::__construct('local'); // define nome do form
        
        foreach ($types as $type) {
            $this->localTypes[$type->id] = $type->name;
        };
        
        $this->add([
            'name' => 'id',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => 'Name',
            ],
        ]);
        $this->add([
            'name' => 'type_id',
            'type' => 'select',
            'options' => [
                'label' => 'Type',
                'value_options' => $this->localTypes,
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id' => 'submitbutton',
            ],
        ]);
    }
}
