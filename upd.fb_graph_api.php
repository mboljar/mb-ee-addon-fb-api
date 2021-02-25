<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fb_graph_api_upd {

	var $version = '1.0.0';
	
	function __construct()
	{
		//$this->EE =& get_instance();
	}
	

	/**
	 * Module Install
	 *
	 * @access	public
	 * @return	bool
	 */

	function install()
	{
        ee()->load->dbforge();
		
		$mod_data=array(
			'module_name' => 'Fb_graph_api',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

        ee()->db->insert('modules', $mod_data);
		
		$fields = array(
			'id'			=> array('type'=>'INT','constraint'=>'2','unsigned'=>TRUE,'auto_increment'=>TRUE),
			'app_id'		=> array('type' => 'VARCHAR','constraint' => '20'),
			'app_secret'	=> array('type' => 'VARCHAR','constraint' => '40'),
			'default_token'	=> array('type' => 'VARCHAR','constraint' => '200'),
            'tokens'        => array('type' => 'TEXT'),
            'created_by'    => array('type' => 'VARCHAR', 'constraint' => '255'),
            'created_date'  => array('type' => 'INT', 'constraint' => '10')
			);

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', TRUE);
        ee()->dbforge->create_table('fb_graph_api');
		
		return TRUE;
	}
	

	/**
	 * Module Uninstall
	 *
	 * @access	public
	 * @return	bool
	 */
	 
	function uninstall()
	{		
		ee()->load->dbforge();

		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => 'Fb_graph_api'));

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_roles');

		ee()->db->where('module_name', 'Fb_graph_api');
        ee()->db->delete('modules');

        ee()->dbforge->drop_table('fb_graph_api');
				
		return TRUE;
	}
	

	/**
	 * Module Update
	 *
	 * @access	public
	 * @return	bool
	 */	
	 
	function update($current = '')
	{

		// If you have updates, drop 'em in here.
		return TRUE;

	}
	
}

/* End of file upd.fb_graph_api.php */
/* Location: ./system/user/addons/fb_graph_api/upd.fb_graph_api.php */