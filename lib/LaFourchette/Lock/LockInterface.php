<?php

namespace LaFourchette\Lock;

/**
 * @author david
 */
interface LockInterface
{
	/**
	 * Acquire the lock.
	 * 
	 * @return boolean True if acquisition successfull. False if not.
	 */
	function acquire($metadata = null);
	
	/**
	 * Release the lock.
	 */
	function release();

	const CHECK_RETURN_LOCKED = 1;
    const CHECK_RETURN_NOLOCK = 0;
    const CHECK_RETURN_DEADLOCK = -1;

	/**
	 * Check if lock is acquired.
	 * 
	 * @return string (lock value) or self:CHECK_RETURN_*
	 */
	function check();
	
	/**
	 * @var array Informations about the lock mechanism.
	 */
	function getInfo();
}