<?php

namespace Scribe\Jabiru\Event;

/**
 * EmitterAwareInterface should be implemented by classes that depends on EmitterInterface
 */
interface EmitterAwareInterface
{

    /**
     * @api
     *
     * @param EmitterInterface $emitter
     *
     * @return mixed
     */
    public function setEmitter(EmitterInterface $emitter);

    /**
     * @api
     *
     * @return EmitterInterface
     */
    public function getEmitter();

}