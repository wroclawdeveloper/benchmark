<?php

namespace App;

/**
 * Timing Class for benchmarking in PHP
 */
class Timing
{
    private $start_time;
    private $stop_time;

    /**
     * @param mixed $start_time
     */
    public function setStartTime()
    {
        $this->start_time = microtime(true);
    }

    /**
     * @param mixed $stop_time
     */
    public function setStopTime()
    {
        $this->stop_time = microtime(true);
    }

    // Returns time elapsed from start

    /**
     * Returns time elapsed from start
     *
     * @return mixed
     */
    public function getElapsedTime()
    {
        return microtime(true) - $this->start_time;
    }


}