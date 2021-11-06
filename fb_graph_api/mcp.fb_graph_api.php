<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include_once dirname(__FILE__).'/config.php';

// autoload Facebook
require __DIR__ . '/vendor/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class Fb_graph_api_mcp
{

	function __construct()
	{
		// Get the DB settings
		$query = ee()->db->get('fb_graph_api');
		if($query->num_rows() > 0)
        {
			$row = $query->row();
			$this->settings = array(
                'id'                => $row->id,
				'app_id'            => $row->app_id,
				'app_secret'        => $row->app_secret,
				'app_graph_ver_min' => $row->app_graph_ver_min,
				'app_graph_ver_max' => $row->app_graph_ver_max,
				'app_graph_ver'     => $row->app_graph_ver,
                'default_token'     => $row->default_token,
                'tokens'            => $row->tokens,
                'created_by'        => $row->created_by,
                'created_date'      => $row->created_date,
			);
		}
        else
        {
			$this->settings = array(
                'id'                => '',
				'app_id'            => '',
				'app_secret'        => '',
				'app_graph_ver_min' => '',
				'app_graph_ver_max' => '',
				'app_graph_ver'     => '',
                'default_token'     => '',
                'tokens'            => '',
                'created_by'        => '',
                'created_date'      => ''
			);
        }

        $fb_sdk = "<script>$(document).ready(function() { $.ajaxSetup({ cache: true });$.getScript('//connect.facebook.net/en_US/sdk.js', function() { FB.init({ appId: '" . $this->settings['app_id'] . "', xfbml: true, cookie: true, version: '" . $this->settings['app_graph_ver'] . "' }); FB.getLoginStatus(function(response) { if(response) { $(\"#fb-error\").hide(); $(\"#fb-authorize\").show(); } });}); });</script>";

        ee()->cp->add_to_foot($fb_sdk);

		// Get the Developer settings
		$query = ee()->db->get('fb_graph_api_dev');
		if($query->num_rows() > 0)
        {
			$row = $query->row();
			$this->dev_settings = array(
                'id'                => $row->id,
                'show_error_msg'    => $row->show_error_msg,
                'pretty_print_json' => $row->pretty_print_json,
                'show_metadata'     => $row->show_metadata
			);
		}
        else
        {
			$this->dev_settings = array(
                'id'                => '',
                'show_error_msg'    => 0,
                'pretty_print_json' => 0,
                'show_metadata'     => 0
			);
        }

	}


	/*
	 * Build the side navigation menu for the module
	 */
	private function _sideNav()
	{
		$sidebar = ee('CP/Sidebar')->make();

		$app_settings = $sidebar->addHeader(lang('app_settings'), ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));

		$dev_settings = $sidebar->addHeader(lang('dev_settings'), ee('CP/URL', 'addons/settings/fb_graph_api/dev_settings'));

		if (version_compare(APP_VER, '6.0.0', '>='))
		{
            $sidebar->addDivider();
        }

		$manual = $sidebar->addHeader(lang('manual'), ee('CP/URL', 'addons/manual/fb_graph_api'));
	}


	public function index()
	{
		// No index, redirect to app settings
		ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings')->compile());
	}

    /**
     * Module settings
     *
     * Users authenticate app- if successful display a "Get Tokens" button - if not display the error (all via Javascript)
     * User clicks "Get Tokens" to run the get_tokens methods (which gets both page and app tokens and stores them) and the page refreshes with the tokens displayed in a radio button select form.
     *
     * @return mixed
     */
	function app_settings()
    {
		$this->_sideNav();

		// Load necessary helpers and libraries
		ee()->load->library('table');
		ee()->load->helper('form');

		// Set page title
		ee()->view->cp_page_title = lang('cp_title') . " : " . lang('app_settings');

		$vars['id'] = NULL;
		$vars['app_id'] = NULL;
		$vars['app_secret'] = NULL;
		$vars['app_graph_ver_min'] = NULL;
		$vars['app_graph_ver_max'] = NULL;
		$vars['app_graph_ver'] = NULL;
		$vars['add_app'] = ee('CP/URL', 'addons/settings/fb_graph_api/add_app');
        $vars['add_token'] = ee('CP/URL', 'addons/settings/fb_graph_api/add_token');
        $vars['get_token'] = ee('CP/URL', 'addons/settings/fb_graph_api/get_tokens');
        $vars['clear_token'] = ee('CP/URL', 'addons/settings/fb_graph_api/clear_tokens');
		$vars['form_hidden'] = NULL;

		if (!empty($this->settings))
        {
            $vars['id'] = $this->settings['id'];
            $vars['form_hidden'] = array('id' => $this->settings['id']);
            $vars['app_id'] = $this->settings['app_id'];
            $vars['app_secret'] = $this->settings['app_secret'];
            $vars['app_graph_ver_min'] = $this->settings['app_graph_ver_min'];
            $vars['app_graph_ver_max'] = $this->settings['app_graph_ver_max'];
            $vars['app_graph_ver'] = $this->settings['app_graph_ver'];
            $vars['default_token'] = $this->settings['default_token'];
            if(!empty($this->settings['tokens']))
            {
                $vars['tokens'] = unserialize($this->settings['tokens']);
            }
            $vars['created_by'] = $this->settings['created_by'];
            $vars['created_date'] = $this->settings['created_date'];
		}

        return ee()->load->view('app_settings', $vars, TRUE);
	}


    /**
     * Save basic app settings
     *
     * Saves the app id and secret from Facebook. We do this first since
     * everything else depends on these.
     */
	function add_app()
    {
		// Load necessary helpers and libraries
		ee()->load->helper('form');

		$data = array(
			'app_id'            => ee()->input->post('app_id'),
			'app_secret'        => ee()->input->post('app_secret'),
			'app_graph_ver_min' => ee()->input->post('app_graph_ver_min'),
			'app_graph_ver_max' => ee()->input->post('app_graph_ver_max'),
			'app_graph_ver'     => ee()->input->post('app_graph_ver')
		);

        $fbid = ee()->db->select('id')->get('fb_graph_api');

		if ($fbid->num_rows() > 0)
        {
            $id = $fbid->row_array();

            // If settings changed then clear the old tokens
            if($data['app_id'] != $this->settings['app_id'] || $data['app_secret'] != $this->settings['app_secret']) {
                $data['default_token'] = NULL;
                $data['tokens']        = NULL;
            }
			ee()->db->update('fb_graph_api', $data, $id);
		}
        else
        {
			ee()->db->insert('fb_graph_api', $data);
		}

        ee('CP/Alert')->makeInline('app_saved')
            ->asSuccess()
            ->withTitle(lang('app_saved'))
            ->defer();

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
	}


    /**
     * Save app token
     *
     * The default token is selected from the available list.
     *
     */
    function add_token()
    {
        // Load necessary helpers and libraries
		ee()->load->helper('form');

        $data = array(
            'default_token' => ee()->input->post("default_token")
        );
        ee()->db->update('fb_graph_api', $data, array('id' => $this->settings['id']));

        ee('CP/Alert')->makeInline('token_status')
            ->asSuccess()
            ->withTitle(lang('token_success'))
            ->defer();

        ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
    }


    /**
     * Get both app and page tokens and store them in the DB for reference.
     * Old tokens are always replaced when this is called.
     *
     * @throws Exception
     */
    function get_tokens()
    {
        ee()->load->library('logger');

        $fb = new Facebook(array(
            'app_id'                => $this->settings['app_id'],
            'app_secret'            => $this->settings['app_secret'],
            'default_graph_version' => $this->settings['app_graph_ver']
        ));

        // Get app token
        $app_token = array(
            'type'  => 'app',
            'token' =>  $fb->getApp()->getAccessToken()->getValue(),
            'name'  =>  'App'
        );

        // Get user token
        $jsHelper = $fb->getJavaScriptHelper();

        try
        {
            $access_token = $jsHelper->getAccessToken();
        }
        catch(FacebookResponseException $e)
        {
            ee('CP/Alert')->makeInline('token_status')
                ->asIssue()
                ->withTitle(lang('token_failure_title'))
                ->addToBody($e->getMessage())
                ->defer();

            ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
        }
        catch(FacebookSDKException $e)
        {
            ee('CP/Alert')->makeInline('token_status')
                ->asIssue()
                ->withTitle(lang('token_failure_title'))
                ->addToBody($e->getMessage())
                ->defer();

            ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
        }

        // Do we have an access token or null
        if (!isset($access_token))
        {
            ee('CP/Alert')->makeInline('token_status')
                ->asIssue()
                ->withTitle(lang('token_failure_title'))
                ->addToBody(lang('token_failure'))
                ->defer();

            ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));

        }
        // We need a long-lived user token
        elseif (isset($access_token))
        {
            // Get a long-lived user token
            try
            {
                $oAuth2Client = $fb->getOAuth2Client();
                $longUserToken = $oAuth2Client->getLongLivedAccessToken($access_token);
            }
            catch (\Exception $ex)
            {
                // Handle a token error
                ee()->logger->developer(FB_GRAPH_API_MOD_NAME . ': ' . $ex->getMessage());
            }

            // Now we can get page tokens
            if (!empty($longUserToken))
            {
                $fb->setDefaultAccessToken($longUserToken->getValue());
                try
                {
                    $pages = $fb->request('GET', '/me/accounts');
                    $user = $fb->request('GET', '/me');
                    $responses = $fb->sendBatchRequest(array($pages, $user));

                    // Work with our tokens
                    foreach($responses as $key => $response)
                    {
                        if ($response->isError())
                        {
                            $e = $response->getThrownException();
                            throw new Exception($e->getMessage(), $e->getCode());
                        }
                        else
                        {
                            $arr = $response->getDecodedBody();
                            // If it's 'data' then it's tokens
                            if(isset($arr['data']))
                            {
                                $stored_tokens = array();
                                foreach ($arr['data'] as $page)
                                {
                                    $page_token = array();
                                    $page_token['type'] = 'page';
                                    $page_token['token'] = $page['access_token'];
                                    $page_token['name'] = $page['name'];
                                    $stored_tokens[] = $page_token;
                                }
                                $stored_tokens[] = $app_token;
                            }
                            else if (isset($arr['name']))
                            {
                                $username = $arr['name'];
                            }
                        }
                    }

                    // Store everything in the DB. Replacing old data.
                    $data = array(
                        'tokens'       => serialize($stored_tokens),
                        'created_by'   => $username,
                        'created_date' => time()
                    );
                    ee()->db->update('fb_graph_api', $data, array('id' => $this->settings['id']));

                }
                catch (FacebookSDKException $e)
                {
                    // Facebook request error
                    ee('CP/Alert')->makeInline('token_status')
                        ->asIssue()
                        ->withTitle(lang('token_failure_title'))
                        ->addToBody($e->getMessage())
                        ->defer();
                }
                catch (\Exception $e)
                {
                    // Some other error
                    ee('CP/Alert')->makeInline('token_status')
                        ->asIssue()
                        ->withTitle(lang('token_failure_title'))
                        ->addToBody($e->getMessage())
                        ->defer();

                }
            }

            ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
        }
    }


    /**
     * Clear all tokens
     *
     * Delete tokens from the DB
     */
    function clear_tokens()
    {
        $data = array(
            'default_token' => NULL,
            'tokens' => NULL
        );
        ee()->db->update('fb_graph_api', $data, array('id' => $this->settings['id']));

        ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/app_settings'));
    }


    /**
     * Developer settings
     *
     * Users authenticate app- if successful display a "Get Tokens" button - if not display the error (all via Javascript)
     * User clicks "Get Tokens" to run the get_tokens methods (which gets both page and app tokens and stores them) and the page refreshes with the tokens displayed in a radio button select form.
     *
     * @return mixed
     */
	function dev_settings()
    {
		$this->_sideNav();

		// Load necessary helpers and libraries
		ee()->load->library('table');
		ee()->load->helper('form');

		// Set page title
		ee()->view->cp_page_title = lang('cp_title') . " : " . lang('dev_settings');

		$vars['id'] = NULL;
		$vars['save_dev_settings'] = ee('CP/URL', 'addons/settings/fb_graph_api/save_dev_settings');
        $vars['show_error_msg'] = 0;
        $vars['pretty_print_json'] = 0;
        $vars['show_metadata'] = 0;

		if (!empty($this->dev_settings))
        {
            $vars['id'] = $this->dev_settings['id'];
            $vars['form_hidden'] = array('id' => $this->dev_settings['id']);
            $vars['show_error_msg'] = $this->dev_settings['show_error_msg'];
            $vars['pretty_print_json'] = $this->dev_settings['pretty_print_json'];
            $vars['show_metadata'] = $this->dev_settings['show_metadata'];
		}

        return ee()->load->view('dev_settings', $vars, TRUE);
	}

    /**
     * Save developer settings
     *
     * Saves the app id and secret from Facebook. We do this first since
     * everything else depends on these.
     */
	function save_dev_settings()
    {
		// Load necessary helpers and libraries
		ee()->load->helper('form');

		$data = array(
			//'id'                => ee()->input->post('id'),
            'show_error_msg'    => ee()->input->post('show_error_msg'),
            'pretty_print_json' => ee()->input->post('pretty_print_json'),
            'show_metadata'     => ee()->input->post('show_metadata')
		);

        $devid = ee()->db->select('id')->get('fb_graph_api_dev');

		if ($devid->num_rows() > 0)
        {
            $id = $devid->row_array();

			ee()->db->update('fb_graph_api_dev', $data, $id);
		}
        else
        {
			ee()->db->insert('fb_graph_api_dev', $data);
		}

        ee('CP/Alert')->makeInline('dev_saved')
            ->asSuccess()
            ->withTitle(lang('dev_saved'))
            ->defer();

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_graph_api/dev_settings'));
	}
}

// END CLASS

/* End of file mcp.fb_graph_api.php */
/* Location: ./system/user/addons/fb_graph_api/mcp.fb_graph_api.php */
//EOF
