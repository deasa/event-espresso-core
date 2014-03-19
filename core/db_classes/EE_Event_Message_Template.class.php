<?php
/**
 * Contains definition for EE_Event_Message_Template model object
 * @package 		Event Espresso
 * @subpackage 	models
 * @since 			4.4
 */
 if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
 * EE_Event_Message_Template
 * This is the model object for EEM_Event_Message_Template
 *
 * @package 		Event Espresso
 * @subpackage 	models
 * @author 			Darren Ethier
 * @since 			4.4
 */
class EE_Event_Message_Template extends EE_Base_Class {

	/**
	 * Primary Key for EEM_Event_Message_Template
	 * @var int
	 */
	protected $_EMT_ID;


	/**
	 * Foreign Key for EEM_Event
	 * @var int
	 */
	protected $_EVT_ID;



	/**
	 * Foreign Key for EEM_Message_Template_Group
	 * @var int
	 */
	protected $_GRP_ID;



	/**
	 * Cached related objects
	 */

	/**
	 * EE_Event model object
	 * @var EE_Event
	 */
	protected $_Event;



	/**
	 * EE_Message_Template_Group model object
	 * @var EE_Message_Template_Group
	 */
	protected $_Message_Template_Group;



	public static function new_instance( $props_n_values = array(), $timezone = NULL ) {
		$classname = __CLASS__;
		$has_object = parent::_check_for_object( $props_n_values, $classname, $timezone );
		return $has_object ? $has_object : new self( $props_n_values, FALSE, $timezone );
	}


	public static function new_instance_from_db ( $props_n_values = array(), $timezone = NULL ) {
		return new self( $props_n_values, TRUE, $timezone );
	}

} //end class EE_Event_Message_Template
