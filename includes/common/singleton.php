<?php

namespace WPCL\QueryEngine\Common;

abstract class Singleton {

	/**
	 * Instances
	 * @since 1.0.0
	 * @access protected
	 * @var (array) $instances : Collection of instantiated classes
	 */
	protected static $instances = array();

	/**
	 * Gets an instance of our class.
	 */
	public static function get_instance( $params = null ) {
		// Use late static binding to get called class
		$class = get_called_class();
		// Get instance of class
		if( !isset(self::$instances[$class] ) ) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}

	/**
	 * Constructor
	 * Though shall not construct that which cannot be constructed
	 * @access private
	 */
	protected function __construct() {
		// Nothing to do here right now
	}
}