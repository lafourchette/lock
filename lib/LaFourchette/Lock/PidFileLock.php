<?php

namespace LaFourchette\Lock;

use LaFourchette\Lock\FileLock;
use LaFourchette\Lock\LockInterface;

class PidFileLock extends FileLock
{
    /**
     * {@inheritdoc}
     * @exception \Exception
     */
    public function acquire($metadata = null)
    {
        if(! is_null($metadata)){
            throw new \Exception('You cannot specify a metadata for this kind of lock !');
        }
        return parent::acquire(getmypid());
    }

    /**
     * Like FileLock::check but if lock file is still present but script is dead will return false instead of true
     *
     * {@inheritdoc}
     */
    public function check()
    {
        $pid = 0 + parent::check();

        if ($pid) {
            exec("ps -p $pid --no-headers", $pids);
            if (! count($pids)) { // Not running anymore !
                return LockInterface::CHECK_RETURN_DEADLOCK;
            }
        }

        return $pid;
    }
}