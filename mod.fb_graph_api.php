<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed.');

// include config file
include_once dirname(__FILE__).'/config.php';

// autoload Facebook
require __DIR__ . '/vendor/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class Fb_graph_api {


	function __construct() {

        // Get the DB settings
        $query = ee()->db->get('fb_graph_api');
        if($query->num_rows() > 0) {
            $row = $query->row();
            $this->settings = array(
                'id'            => $row->id,
                'app_id'		=> $row->app_id,
                'app_secret'	=> $row->app_secret,
                'default_token' => $row->default_token,
                'tokens'        => $row->tokens
            );
        }
    }
    

    /**
     * Primary function
     *
     * @return string
     */
	function get() {

		// Load Typography Class to parse data
		ee()->load->library('typography');
		ee()->typography->initialize();
		ee()->load->helper('url');
        ee()->load->helper('fb_parse_helper');
	
		$output = '';

		$params = array(
            'token'             =>  ee()->TMPL->fetch_param('token', $this->settings['default_token']),
            'node_id'	        =>  ee()->TMPL->fetch_param('node_id'),
            'edge'	            =>  ee()->TMPL->fetch_param('edge'),
            'fields'	        =>  ee()->TMPL->fetch_param('fields'),
            'include_canceled'  =>  ee()->TMPL->fetch_param('include_canceled', 'false'),
            'since'	            =>  ee()->TMPL->fetch_param('since'),
            'until'	            =>  ee()->TMPL->fetch_param('until'),
            'sort'	            =>  ee()->TMPL->fetch_param('sort'),
            'order'	            =>  ee()->TMPL->fetch_param('order'),
            'limit'             =>  ee()->TMPL->fetch_param('limit'),
            'json'          	=>  ee()->TMPL->fetch_param('json', 'false')
		);

        $request = $params['node_id'] . "/";
        if ( $params['edge'] != '' ) {
            $request = $request . $params['edge'];
        }
        $request = $request . "?fields=" . $params['fields'];
        if ( $params['include_canceled'] == 'true' ) {
            $request = $request . "&include_canceled=true";
        }
        if ( $params['since'] != '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['since']) ) {
            $request = $request . "&since=" . $params['since'];
        }
        if ( $params['until'] != '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['until']) ) {
            $request = $request . "&until=" . $params['until'];
        }
        if ( $params['sort'] != '' ) {
            $request = $request . "&sort=" . $params['sort'];
        }
        if ( $params['order'] != '' ) {
            $request = $request . "&order=" . $params['order'];
        }
        if ( $params['limit'] != '' ) {
            $request = $request . "&limit=" . $params['limit'];
        }

        $fb = new Facebook(array(
            'app_id' => $this->settings['app_id'],
            'app_secret' => $this->settings['app_secret'],
            'default_graph_version' => FACEBOOK_GRAPH_VERSION
        ));

		try {
            // We need to set the index for the parser later
            $response = $fb->get($request, $params['token']);
        } catch (FacebookResponseException $e) {
            ee()->load->library('logger');
            ee()->logger->developer('FB Graph Tag Error: ' . $e->getMessage());
            // Return empty output
            return $output;
		} catch (FacebookSDKException $e) {
            // Log error
			ee()->load->library('logger');
            ee()->logger->developer('FB Graph Tag Error: ' . $e->getMessage());
            // Return empty output
            return $output;
		}

		// We need to make some "rows" for the EE parser.
		$rows[] = make_rows($response->getDecodedBody());

        if (!empty($params['limit'])) {
            array_slice($rows[0], 0, $params['limit']);
        }

        if($params['json'] == 'true') {
            // Output our JSON here
            ee()->output->send_ajax_response($rows[0]);
        }

    /*
		//
		// This may be handy for pagination later but for now it's just filed away.
		//
		if (preg_match("/".LD."paging".RD."(.+?)".LD.'\/'."paging".RD."/s", $this->EE->TMPL->tagdata, $page_match)) {
			// The pattern was found and we set aside the paging tagdata for later and created a copy of all the other tagdata for use
			$paging = $page_match[1];
			// Replace the {paging} variable pairs with nothing and set this aside for later.
			$tag_data = preg_replace("/".LD."paging".RD.".+?".LD.'\/'."paging".RD."/s", "", $this->EE->TMPL->tagdata);
		*/
		
		$tag_data = ee()->TMPL->tagdata;
						
		$output = ee()->TMPL->parse_variables($tag_data, $rows);
														
		return $output;
		
	}
}

/* End of file mod.fb_graph_api.php */
/* Location: ./system/user/addons/fb_graph_api/mod.fb_graph_api.php */