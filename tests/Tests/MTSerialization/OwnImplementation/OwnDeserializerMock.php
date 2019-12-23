<?php

namespace Tests\MTSerialization\OwnImplementation;

use MTSerialization\OwnImplementation\OwnDeserializer;

class OwnDeserializerMock extends OwnDeserializer
{

    public function __construct()
    {
        parent::__construct();
        $this->extendMapWithTestMocks();
    }

    private function extendMapWithTestMocks()
    {
        $this->extendMap('{
            "constructors": [
                {
                    "id": "-1999999999",
                    "predicate": "inputMediaPhoto_TestEdition",
                    "params": [
                        {
                            "name": "flags",
                            "type": "#"
                        },
                        {
                            "name": "some_optional_int_1",
                            "type": "flags.2?int"
                        },
                        {
                            "name": "some_optional_long",
                            "type": "flags.0?long"
                        },
                        {
                            "name": "some_int",
                            "type": "int"
                        },
                        {
                            "name": "some_optional_int_2",
                            "type": "flags.1?int"
                        },
                        {
                            "name": "some_optional_true",
                            "type": "flags.3?true"
                        }
                    ],
                    "type": "InputMedia"
                }
            ],
            "methods": []
        }');
    }


}