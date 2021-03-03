<?php

namespace Local\Form;

use Laminas\Form\Form;

class LocalForm extends Form {
    public function __construct($name = null)
    {
        parent::__construct('local'); // define nome do form
        
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
                'value_options' => [ //estatico, precisa fazer consulta no banco
                    '1' => 'Restaurant',
                    '2' => 'Soccer court',
                    '3' => 'Square'
                ]
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
