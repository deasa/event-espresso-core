<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
	exit('No direct script access allowed');
}

/**
 *
 * EE_Register_Model_Test
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 *
 */
/**
 * @group core/libraries/plugin_api
 * @group agg
 */
class EE_Register_Model_Extensions_Test extends EE_UnitTestCase{
	private $_reg_args;
	private $_model_group;
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$this->_reg_args = array(
			'model_extension_paths' => array( EE_MOCKS_DIR . 'core/db_model_extensions/' ),
			'class_extension_paths' => array( EE_MOCKS_DIR . 'core/db_class_extensions/' )
		);
		$this->_model_group = 'Mock';
		parent::__construct($name, $data, $dataName);
	}
	/**
	 * Determines if the attendee class has been extended by teh mock extension
	 * @return boolean
	 */
	private function _class_has_been_extended( $throw_error = FALSE){
		try{
			$a = EE_Attendee::new_instance();
			$a->foobar();
			return TRUE;
		}catch(EE_Error $e){
			if( $throw_error ){
				throw $e;
			}
			return FALSE;
		}
	}

	/**
	 * Determines if the Attendee model has been extended by the mock extension
	 * @return boolean
	 */
	private function _model_has_been_extended( $throw_error = FALSE){
		try{
			$att = EE_Registry::instance()->load_model('Attendee');
			$att->reset()->foobar();
			if( ! $att->has_field('ATT_foobar')){
				if( $throw_error ){
					throw new EE_Error(sprintf( __( 'The field ATT_foobar is not on EEM_Attendee, but the extension should have added it. fields are: %s', 'event_espresso' ), implode(",",array_keys(EEM_Attendee::instance()->field_settings()))));
				}
				return FALSE;
			}
			if( ! $att->has_relation('Transaction')){
				if( $throw_error ){
					throw new EE_Error(sprintf( __( 'The relation of type Transaction on EEM_Attendee, but the extension should have added it. fields are: %s', 'event_espresso' ), implode(",",array_keys(EEM_Attendee::instance()->field_settings()))));
				}
				return FALSE;
			}
			return TRUE;
		}catch(EE_Error $e){
			if( $throw_error ){
				throw $e;
			}
			return FALSE;
		}
	}

	//test registering a bare minimum addon, and then deregistering it
	function test_register_mock_model_fail(){
		//we're registering the addon WAAAY after EE_System has set thing up, so
		//registering this first time should throw an E_USER_NOTICE
		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
		try{
			EE_Register_Model_Extensions::register($this->_model_group, $this->_reg_args);
			$this->fail('We should have had a warning saying that we are setting up the ee addon at the wrong time');
		}catch(EE_Error $e){
			$this->assertTrue(True);
		}
		//verify they still haven't been extended
		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
	}

	function test_register_mock_model_extension_fail__bad_parameters(){
		//we're registering the addon with the wrong parameters
		$this->_pretend_addon_hook_time();
		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
		try{
			EE_Register_Model_Extensions::register($this->_model_group, array('foo' => 'bar'));
			$this->fail('We should have had a warning saying that we are setting up the ee addon at the wrong time');
		}catch(EE_Error $e){
			$this->assertTrue(True);
		}
		//verify they still haven't been extended
		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
	}

	protected function _pretend_addon_hook_time() {
		global $wp_actions;
		unset( $wp_actions['AHEE__EEM_Attendee__construct__end'] );
		parent::_pretend_addon_hook_time();
	}

	function test_register_mock_addon_success(){

		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
		$this->_pretend_addon_hook_time();

		EE_Register_Model_Extensions::register($this->_model_group, $this->_reg_args);
		$att_model = EE_Registry::instance()->reset_model('Attendee');
//		$att_model::reset();
//		EEM_Attendee::reset();
//
//		var_dump( EE_Registry::instance() );
		//verify they still haven't been extended
		$this->assertTrue( $this->_class_has_been_extended( TRUE ) );
		$this->assertTrue( $this->_model_has_been_extended( TRUE ) );
		//and that we can still use EEM_Attendee
		$a = EE_Attendee::new_instance();
		EEM_Attendee::instance()->get_all();
		EE_Registry::instance()->load_model('Attendee')->get_all();

		//now deregister it
		EE_Register_Model_Extensions::deregister($this->_model_group);
		$this->assertFalse( $this->_class_has_been_extended() );
		$this->assertFalse( $this->_model_has_been_extended() );
		//and EEM_Attendee still works right? both ways of instantiating it?
		$a2 = EE_Attendee::new_instance();
		EEM_Attendee::instance()->get_all();
		EE_Registry::instance()->load_model('Attendee')->get_all();

	}
	public function setUp(){
		parent::setUp();
		EE_Registry::instance()->load_helper('Activation');
		EEH_Activation::add_column_if_it_doesnt_exist('esp_attendee_meta', 'ATT_foobar');
	}
	public function tearDown(){
		//ensure the models aren't stil registered. they should have either been
		//deregistered during the test, or not been registered at all
		$this->_stop_pretending_addon_hook_time();
		$att_model_a = EE_Registry::instance()->load_model( 'Attendee' );
		$att_model_a::reset();
		EEM_Attendee::reset();

		parent::tearDown();
	}
}

// End of file EE_Register_Model_Test.php