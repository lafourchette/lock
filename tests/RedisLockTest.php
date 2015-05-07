<?php

use LaFourchette\Lock\RedisLock;
use LaFourchette\Lock\LockInterface;

/**
 * @author danielb
 */
class RedisLockTest extends \PHPUnit_Framework_TestCase
{
    protected function getRedisConnexionMock()
    {
      return $this->getMockBuilder('Redis')
            ->setMethods(array('get', 'set', 'delete'))
            ->getMock();
    }

    public function testGetLockKey()
    {
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        
        $this->assertNull($redisLock->getLockKey());
    }
    
    public function testSetLockKey()
    {
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        
        $redisLock->setLockKey('key1');
        $this->assertEquals($redisLock->getLockKey(), 'key1');
    }
    
    public function testAcquire()
    {
        // Exception 
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        try {
            $redisLock->acquire();
        } catch (LogicException $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
        
        // Content Lock Exist
        $this->redisConnexion = $this->getRedisConnexionMock();
        $this->redisConnexion->expects($this->any())
            ->method('get')
            ->will($this->returnValue('value1'));
        
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('key1');
        $this->assertFalse($redisLock->acquire());
        
        // Set Key Value with success
        $this->redisConnexion = $this->getRedisConnexionMock();
        $this->redisConnexion->expects($this->any())
            ->method('set')
            ->will($this->returnValue(true));
        
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('key1');
        $this->assertTrue($redisLock->acquire());
        
        // Set Key Value with failure
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('key1');
        $this->assertFalse($redisLock->acquire());
    }
    
    public function testCheck()
    {
        // Exception 
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        try {
            $redisLock->check();
        } catch (LogicException $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
        
        // Get lock not exist
        $this->redisConnexion = $this->getRedisConnexionMock();
        $this->redisConnexion->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));
        
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('value1');
        $this->assertEquals($redisLock->check(), LockInterface::CHECK_RETURN_NOLOCK);
        
        // Get lock exist
        $this->redisConnexion = $this->getRedisConnexionMock();
        $this->redisConnexion->expects($this->any())
            ->method('get')
            ->will($this->returnValue('value1'));
        
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('value1');
        $this->assertEquals($redisLock->check(), 'value1');
    }
    
    public function testRelease()
    {
        // Exception
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        try {
            $redisLock->release();
        } catch (LogicException $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
        
        // Delete lock
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        $redisLock->setLockKey('value1');
        
        $this->assertEquals($redisLock->release(), LockInterface::CHECK_RETURN_DEADLOCK);
    }
    
    public function testGetInfo()
    {
        $this->redisConnexion = $this->getRedisConnexionMock();
        $redisLock = new RedisLock($this->redisConnexion);
        
        $this->assertEquals($redisLock->getInfo(), array(
            'lock_key' => null,
        ));
    }
}
