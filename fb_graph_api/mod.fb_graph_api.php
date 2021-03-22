<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed.');

// include config file
include_once dirname(__FILE__).'/config.php';

// autoload Facebook Graph SDK
require __DIR__ . '/vendor/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class Fb_graph_api
{

	function __construct()
    {

		// Get the DB settings
		$query = ee()->db->get('fb_graph_api');
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->settings = array(
				'id'            => $row->id,
				'app_id'        => $row->app_id,
				'app_secret'    => $row->app_secret,
				'default_token' => $row->default_token,
				'tokens'        => $row->tokens
			);
		}

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

        $this->uristr = ee()->uri->uri_string();
    }

    /**
     * Primary function
     *
     * @return string
     */
	public function get()
    {
		// Load Typography Class to parse data
		ee()->load->library('typography');
		ee()->typography->initialize();
		ee()->load->helper('url');
        ee()->load->helper('fb_graph_api_helper');

        $request     = '';
		$output      = '';
		$error_title = '';
		$error_msg   = '';

		$params = array(
            'token'            => ee()->TMPL->fetch_param('token', $this->settings['default_token']),
            'node'             => ee()->TMPL->fetch_param('node'),
            'edge'             => ee()->TMPL->fetch_param('edge'),
            'fields'           => ee()->TMPL->fetch_param('fields'),
            'include_canceled' => ee()->TMPL->fetch_param('include_canceled', 'false'),
            'since'            => ee()->TMPL->fetch_param('since'),
            'until'            => ee()->TMPL->fetch_param('until'),
            'sort'             => ee()->TMPL->fetch_param('sort'),
            'order'            => ee()->TMPL->fetch_param('order'),
            'limit'            => ee()->TMPL->fetch_param('limit'),
            'paging'           => ee()->TMPL->fetch_param('paging', ''),
            'json'             => ee()->TMPL->fetch_param('json', 'false')
		);

		// Build the request
		$request = $params['node'] . "/";
		if ( ! empty($params['edge']))
		{
			$request .= $params['edge'];
		}

		// prepare for query string
		$request .= "?";

		if ( ! empty($params['fields']))
		{
			$request .= "fields=" . $params['fields'];
		}

		if ($params['include_canceled'] === 'true')
		{
			$request .= "&include_canceled=true";
		}

		if ( ! empty($params['since']) && (preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['since']) OR preg_match('/^\d{10}$/', $params['since'])))
		{
			$request .= "&since=" . $params['since'];
		}

		if ( ! empty($params['until']) && (preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['until']) OR preg_match('/^\d{10}$/', $params['until'])))
		{
			$request .= "&until=" . $params['until'];
		}

		if ( ! empty($params['sort']))
		{
			$request .= "&sort=" . $params['sort'];
		}

		if ( ! empty($params['order']))
		{
			$request .= "&order=" . $params['order'];
		}

		if ( ! empty($params['limit']))
		{
			$request .= "&limit=" . $params['limit'];
		}

		if ($this->dev_settings['show_metadata'] === 1)
		{
			$request .= "&metadata=1";
		}

		// due to unexpected results the 'before' cursor will not be used, browser (window) history will be used instead.
		// leaving this here for potential future use.
		if (preg_match('#/previous/(.*)#s', $this->uristr, $matches))
		{
			$request .= '&before='.$matches[1];
		}

		if (preg_match('#/next/(.*)#s', $this->uristr, $matches))
		{
			$request .= '&after='.$matches[1];
		}

		$fb = new Facebook(array(
			'app_id'                => $this->settings['app_id'],
			'app_secret'            => $this->settings['app_secret'],
			'default_graph_version' => FACEBOOK_GRAPH_VERSION
		));

		// get the data from Facebook
		try
		{
			// We need to set the index for the parser later
			$response = $fb->get($request, $params['token']);
		}
		catch (FacebookResponseException $e)
		{
			$error_title = lang('err_fb_resp_title');
			$error_msg   =  $e->getMessage();
			// Log error message
			ee()->load->library('logger');
			ee()->logger->developer($error_title . $error_msg);

			if ($this->dev_settings['show_error_msg'] === 1)
			{
				// Return error message
				return create_error_msg($error_title, $error_msg);
			}
			else
			{
				// return empty output
				return $output;
			}
		}
		catch (FacebookSDKException $e)
		{
			$error_title = lang('err_fb_sdk_title');
			$error_msg   = $e->getMessage();
			// Log error message
			ee()->load->library('logger');
			ee()->logger->developer($error_title . $error_msg);

			if ($this->dev_settings['show_error_msg'] === 1)
			{
				// Return error message
				return create_error_msg($error_title, $error_msg);
			}
			else
			{
				// return empty output
				return $output;
			}
		}

		// store Facebook's response as an array.
		$fb_data_array = $response->getDecodedBody();

		// We need to make some "rows" for the EE parser.
		$rows[] = make_rows($fb_data_array);

		// add top level key 'data' to our rows array, if absent, for consistent tag output
		if ( ! array_key_exists('data', $rows[0]))
		{
			$rows[0] = array('data' => array(0 => $rows[0]));
		}

		// Output in JSON? Let's do it and get out of here
		if ($params['json'] === 'true')
		{
			// add top level key 'data' to the original Facebook response, if absent, for consistent JSON output
			if ( ! array_key_exists('data', $fb_data_array))
			{
				$fb_data_array = array('data' => array(0 => $fb_data_array));
			}

			// Output our pure Facebook JSON here with top level key 'data', and exit
			// e.g. { "data" : [ { "key 1" : "value 1" }, { "key 2" : "value 2" } ] }
			if ($this->dev_settings['pretty_print_json'] === 1)
			{
				$this->output_json($fb_data_array, TRUE);
			}
			else
			{
				$this->output_json($fb_data_array);
			}
		}

		// store tag data
		$tag_data = ee()->TMPL->tagdata;

		// paging links wanted?
		if ( ! empty($params['paging']) && preg_match('/^(bottom|top|both)$/', $params['paging']))
		{
			// any paging present in the Facebook response?
			if (array_key_exists('paging', $rows[0]))
			{
				// paging is present. store it
				$paging = $rows[0]['paging'][0];

				// so there must also be cursors. store them too
				$paging_cursors = $paging['paging:cursors'][0];

				// check for a 'next' page
				if (array_key_exists('paging:next', $paging) )
				{
					// yes there is a next page. store its cursor
					$paging_cursor_after = $paging_cursors['cursors:after'];
				}
				else
				{
					$paging_cursor_after = '';
				}

				// check for a 'previous' page.
				if (array_key_exists('paging:previous', $paging))
				{
					// yes there is a previous page. store its cursor
					$paging_cursor_before = $paging_cursors['cursors:before'];
				}
				else
				{
					$paging_cursor_before = '';
				}

				// let's separate browsing tags from data tags
				if (strpos($tag_data, LD . 'paging' . RD) !== FALSE
                	&& preg_match_all("/" . LD . "paging" . RD . "(.+?)" . LD . '\/' . "paging" . RD . "/s", $tag_data, $paging_match))
				{
					// store paging tags
					$paging_tag_data = implode($paging_match[1]);

					// create paging links output
					$paging_links = create_paging_links($paging_tag_data, $paging_cursor_after, $paging_cursor_before);

					// remove paging tags, we'll prepend and/or append them later
					$tag_data = str_replace($paging_match[0], '', $tag_data);

					// create data output
					$output = ee()->TMPL->parse_variables($tag_data, $rows[0]['data']);

					// Return the data with the paging links where they're wanted
					switch ($params['paging'])
					{
						case "top":
							return $paging_links . $output;
							break;

						case "both":
							return $paging_links . $output . $paging_links;
							break;

						case "bottom":
						default:
							return $output . $paging_links;
							break;
					}
				}
				else
				// paging links wanted, but no paging tags found.
				// return error message
				{
					$error_title = lang('err_paging_title');
					$error_msg   = lang('err_paging_msg');

					if ($this->dev_settings['show_error_msg'] === 1)
					{
						// Return error message
						return create_error_msg($error_title, $error_msg);
					}
					else
					{
						// return empty output
						return $output;
					}
				}
			}
		}
		else
		{
			// remove top level array key 'data', we don't need it for tag output
			$rows = $rows[0]['data'];

			$output = ee()->TMPL->parse_variables($tag_data, $rows);

			return $output;
		}
	}

    /**
     * Send JSON output
     *
     * Outputs JSON and exits
     *
     * @access	public
     * @param	string	the output data
     * @param	bool	whether or not to pretty-print JSON
     * @return	void
     */
	public function output_json($data, $pretty_print = FALSE)
	{

		if (ee()->config->item('send_headers') === 'y')
		{
			ee()->load->library('user_agent', array(), 'user_agent');

			// many browsers do not consistently like this content type
			if (is_array($data) && in_array(ee()->user_agent->browser(), array('Safari', 'Chrome', 'Firefox')))
			{
				@header('Content-Type: application/json; charset=UTF-8');
			}
			else
			{
				@header('Content-Type: text/html; charset=UTF-8');
			}
		}

		// pretty-print output?
		if ($pretty_print === TRUE)
		{
			$output = json_encode($data, JSON_PRETTY_PRINT);
		}
		else
		{
			$output = json_encode($data);
		}

		exit($output);
	}

}
/* End of file mod.fb_graph_api.php */
/* Location: ./system/user/addons/fb_graph_api/mod.fb_graph_api.php */
// EOF
