<?php
/**
 * API_Manager handles registering actions and hooks with the
 * WordPress Plugin API.
 */

namespace WPCL\QueryEngine;

class Loader extends \WPCL\QueryEngine\Common\Singleton  {

	/**
	 * Registers an object with the WordPress Plugin API.
	 * @param mixed $object
	 */
	public function register( $object ) {
		// Register Actions
		if ( $object instanceof \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber ) {
			$this->register_actions( $object );
		}
		// Register Filters
		if ( $object instanceof \WPCL\QueryEngine\Interfaces\Filter_Hook_Subscriber ) {
			$this->register_filters( $object );
		}
		// Register Shortcodes
		if ( $object instanceof \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber ) {
			$this->register_shortcodes( $object );
		}
	}

	/**
	 * Register an object with a specific action hook.
	 * @param Action_Hook_Subscriber $object
	 * @param string $name
	 * @param mixed $parameters
	 */
	private function register_action( \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber $object, $name, $parameters ) {

		if( is_string( $parameters ) ) {

			add_action( $name, array( $object, $parameters ) );
		}

		elseif( is_array( $parameters ) && isset( $parameters[0] ) ) {
			add_action( $name, array( $object, $parameters[0] ), isset( $parameters[1] ) ? $parameters[1] : 10, isset( $parameters[2] ) ? $parameters[2] : 1 );
		}
	}

	/**
	 * Regiters an object with all its action hooks.
	 *
	 * @param Action_Hook_SubscriberInterface $object
	 */
	private function register_actions( \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber $object ) {
		foreach( $object->get_actions() as $action ) {
			$this->register_action( $object, key( $action ), current( $action ) );
		}
	}

	/**
	 * Register an object with a specific filter hook.
	 *
	 * @param Filter_Hook_SubscriberInterface $object
	 * @param string                          $name
	 * @param mixed                           $parameters
	 */
	private function register_filter( \WPCL\QueryEngine\Interfaces\Filter_Hook_Subscriber $object, $name, $parameters ) {

		if( is_string($parameters)) {
			add_filter($name, array($object, $parameters));
		}

		elseif( is_array( $parameters ) && isset( $parameters[0] ) ) {
			add_filter( $name, array( $object, $parameters[0] ), isset( $parameters[1] ) ? $parameters[1] : 10, isset( $parameters[2] ) ? $parameters[2] : 1 );
		}
	}

	/**
	 * Regiters an object with all its filter hooks.
	 *
	 * @param Filter_Hook_SubscriberInterface $object
	 */
	private function register_filters( \WPCL\QueryEngine\Interfaces\Filter_Hook_Subscriber $object) {

		foreach( $object->get_filters() as $filter ) {
			$this->register_filter( $object, key( $filter ), current( $filter ) );
		}
	}

	/**
	 * Register an object with a specific shortcode hook.
	 *
	 * @param Shortcode_Hook_SubscriberInterface $object
	 * @param string                          $name
	 * @param mixed                           $parameters
	 */
	private function register_shortcode( \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber $object, $name, $parameters ) {
		if( is_string( $parameters )) {
			add_shortcode( $name, array( $object, $parameters ) );
		}
	}

	/**
	 * Regiters an object with all its shortcode hooks.
	 *
	 * @param Shortcode_Hook_SubscriberInterface $object
	 */
	private function register_shortcodes( \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber $object) {
		foreach( $object->get_shortcodes() as $shortcode ) {
			$this->register_shortcode( $object, key( $shortcode ), current( $shortcode ) );
		}
	}
}