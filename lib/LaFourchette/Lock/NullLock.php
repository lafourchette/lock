<?php

namespace LaFourchette\Lock;

/**
 * Implements a null lock that can be either always or never locked.
 *
 * @author david
 */
use LaFourchette\Lock\LockInterface;
use Symfony\Component\Validator\Constraints\All;

class NullLock implements LockInterface
{
    protected $isLocked = null;

    /**
     * @param bool $isLocked States this lock status.
     */
    public function __construct($isLocked = false)
    {
        $this->isLocked = (bool) $isLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function acquire($metadata = null)
    {
        return ! $this->isLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function release()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        if($this->isLocked){
            return LockInterface::CHECK_RETURN_LOCKED;
        }
        else{
            return LockInterface::CHECK_RETURN_NOLOCK;
        }
    }
    
    public function getInfo(){return array();}
}