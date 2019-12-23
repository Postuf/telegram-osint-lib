<?php

namespace Client;

/*
 * Client with repeatable actions
 */
interface PeriodicClient
{

    /**
     * Reports to client that he has time to do periodic stuff
     */
    public function onPeriodAvailable(): void;
}