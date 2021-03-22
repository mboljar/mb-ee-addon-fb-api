<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include_once dirname(__FILE__).'/config.php';

class Fb_graph_api_upd {

    public $version = FB_GRAPH_API_MOD_VER;

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

		// create App Settings table
		$fields = array(
			'id'                => array('type' => 'INT', 'constraint' =>'2', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'app_id'            => array('type' => 'VARCHAR', 'constraint' => '20'),
			'app_secret'        => array('type' => 'VARCHAR', 'constraint' => '40'),
			'default_token'     => array('type' => 'VARCHAR', 'constraint' => '200'),
            'tokens'            => array('type' => 'TEXT'),
			'created_by'        => array('type' => 'VARCHAR', 'constraint' => '255'),
			'created_date'      => array('type' => 'INT', 'constraint' => '10')
		);

		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('id', TRUE);
		ee()->dbforge->create_table('fb_graph_api');

		// create Developer Settings table
		$dev_fields = array(
			'id'                => array('type' => 'INT', 'constraint' =>'2', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'show_error_msg'    => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0),
			'pretty_print_json' => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0),
			'show_metadata'     => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0)
		);

		ee()->dbforge->add_field($dev_fields);
		ee()->dbforge->add_key('id', TRUE);
		ee()->dbforge->create_table('fb_graph_api_dev');

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

		if (version_compare(APP_VER, '6.0.0', '<'))
		{
			ee()->db->delete('module_member_groups');
		}
		else
		{
			ee()->db->delete('module_member_roles');
		}

		ee()->db->where('module_name', 'Fb_graph_api');
		ee()->db->delete('modules');

		ee()->dbforge->drop_table('fb_graph_api');
		ee()->dbforge->drop_table('fb_graph_api_dev');

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
		if (version_compare($current, '1.1.0', '<'))
		{
			ee()->load->dbforge();

			// create Developer Settings table
			$dev_fields = array(
				'id'                => array('type' => 'INT', 'constraint' =>'2', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'show_error_msg'    => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0),
				'pretty_print_json' => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0),
				'show_metadata'     => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 0)
			);

			ee()->dbforge->add_field($dev_fields);
			ee()->dbforge->add_key('id', TRUE);
			ee()->dbforge->create_table('fb_graph_api_dev');

			// Delete deprecated helper file
			$delete_file = dirname(__FILE__).'/helpers/fb_parse_helper.php';
			$file_name = 'fb_parse_helper.php';
			// Let's log the event
			ee()->load->library('logger');
			if (!unlink($delete_file))
			{
				ee()->logger->developer(FB_GRAPH_API_MOD_NAME . " : " . lang('log_deprecated_helper') . " '$file_name' " . lang('log_dhp_not_deleted'));
			}
			else
			{
				ee()->logger->developer(FB_GRAPH_API_MOD_NAME . " : " . lang('log_deprecated_helper') . " '$file_name' " . lang('log_dhp_deleted'));
			}
		}

        return TRUE;

	}

}

/* End of file upd.fb_graph_api.php */
/* Location: ./system/user/addons/fb_graph_api/upd.fb_graph_api.php */
