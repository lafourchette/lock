<?php

use LaFourchette\Lock\LockInterface;
use LaFourchette\Lock\PidFileLock;

/**
 * @author david_moreau
 */
class LockTest extends \PHPUnit_Framework_TestCase
{
    function testPidLock()
    {
        /*
        $locks = array(
            array(new PidFileLock()),
        );
        */
        $this->assertTrue(true);
        return new PidFileLock();
    }

    /**
     * @depends testPidLock
     */
    function testAcquisition($lock){
        // No lock yet
        $this->assertEquals(LockInterface::CHECK_RETURN_NOLOCK, $lock->check());
        $this->assertTrue($lock->acquire(), 'Cannot acquire lock');
        // Got a lock
        $this->assertRegExp('/\d{2,}/',(string) $lock->check());
        $this->assertFalse($lock->acquire(), 'Should not acquire a locked lock');
        // Release it
        $lock->release();
        $this->assertEquals(LockInterface::CHECK_RETURN_NOLOCK, $lock->check());

        return $lock;
    }

    /**
     * @depends testAcquisition
     */
    function testFileLockFailures($lock)
    {
        // test for actually releasing file
        $lock->release();
        $this->assertFalse(file_exists($lock->getPath()), 'Release is not releasing the lock. Please manually delete ' . $lock->getPath());
    }

    /*
    $this->assertTrue($fixture->acquire(), 'Cannot acquire lock');
    file_put_contents($path, '65365');
    $this->assertEquals(LockInterface::CHECK_RETURN_DEADLOCK, $fixture->check()); // check does autorelease if dead
    $this->assertTrue(file_exists($path), 'Check is releasing the lock. That is bad.');
    $fixture->release();
    $this->assertFalse(file_exists($path), 'Release is not releasing the lock. Please manually delete ' . $path);
     */
}