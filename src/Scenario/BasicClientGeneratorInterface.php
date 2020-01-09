<?php

namespace Scenario;

use Client\BasicClient\BasicClient;

interface BasicClientGeneratorInterface
{
    public function generate(): BasicClient;
}
