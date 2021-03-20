<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');


/**
 * Create rows for the EE parser
 *
 * Some FB data is an array that is not indexed. For example the from data is an associative array.  THe EE parser needs a "row" to work with. This function will recursively work through the data and if an array is not indexed will create the index. It's a beast of a function but necessary for now and should be flexible enough to cope with FB structure changes.
 *
 * @param $array
 * @param null $parent
 *
 * @return array
 */
function make_rows($array, $parent = NULL)
{
    $var = array();

    // Work through each item to catch arrays and format them for parsing
    foreach ($array as $k => $v)
    {
        // Not an array so pass over to the formatting function after adding the parent key to the current key.
        // This is necessary for the EE parser which trips when keys have the same name - regardless of array context.
        if ( ! is_array($v) && $parent != NULL)
        {
            $var[$parent . ':' . $k] = format($k, $v);

            // Create a permalink from post id if the key is id and it matches the pattern
            if ($k == 'id' && (preg_match('/^\d+_{1}\d+$/', $v)) === 1)
            {
                $p = explode('_', $v);
                $var[$parent . ':permalink'] = 'http://www.facebook.com/' . $p[0] . '/posts/' . $p[1];
                unset($p);
            }

        }
        elseif ( ! is_array($v))
        {
            $var[$k] = format($k, $v);

        }
        // Is an array so hold up we need some more work.
        elseif (is_array($v))
        {
            // We need to rename the "row" named data based on it's parent or else the parser gets confused.
            // Here we rename it and merge the array up to eliminate any unnecessary tag.
            if (isset($v['data']) && !is_numeric(current(array_keys($v['data']))))
            {
                $v[$k . ':data'][] = $v['data'];
                unset($v['data']);
            }
            elseif (isset($v['data']) && is_numeric(current(array_keys($v['data']))))
            {
                $v[$k . ':data'] = $v['data'];
                unset ($v['data']);
            }

            // If it's not numeric we need to create that.
            if ( ! is_numeric($k) && ! is_numeric(current(array_keys($v))))
            {
                if ($parent != NULL)
                {
                    $var[$parent . ':' . $k][0] = make_rows($v, $k);
                }
                else
                {
                    $var[$k][0] = make_rows($v, $k);
                }
            }
            else
            {
                $var[$k] = make_rows($v, $parent);
            }
        }
    }

    return $var;
}


/**
 * Formatter for auto links
 * @param $k
 * @param $v
 *
 * @return mixed
 */
function format($k, $v)
{
    if (($k == 'message') || ($k == 'story'))
    {
        // As of January 2013 'auto_links' is set to no.  The Graph now returns formatted links inside of posts.  If links are causing problems set 'auto_links' to yes and see if that corrects the issue.
        $v = auto_link(ee()->typography->parse_type($v, array('text_format' => 'lite', 'html_format' => 'safe', 'auto_links' => 'n')));
    }

    return $v;
}


/**
 * create links for "previous", "first" and "next" pages
 *
 * @param string $template The template for the paging links
 * @param string $after The 'after' cursor from Facebook
 * @param string $before The 'before' cursor from Facebook
 * @return string
 */
function create_paging_links($template, $after, $before )
{
	$base_segments = '';
	$current_url   = current_url();
	$totalsegments = ee()->uri->total_segments();
	$prev_url = '';
	$first_url = '';
	$next_url = '';
	$prev_text = 'previous';
	$first_text = 'first';
	$next_text = 'next';

	if (preg_match('#(.+)/(previous|next)/(.+)#s', $current_url, $matches))
	{
		$base_segment_count = $totalsegments - 2; // substract 'previous' or 'next', and 'cursor string' segments
		// get base segments
		for ($i = 0; $i < $base_segment_count; $i++)
		{
			$base_segments .= '/' . ee()->uri->segment($i+1);
		}

		// append base segments
		$base_url = site_url('/' . $base_segments);
	}
	else
	{
		// add-on is called from the root of the website
		$base_url = $current_url;
	}

	if ( ! empty($before))
	{
		// We're not on the first page

		// 'before' cursor produced unexpected results. changed to browser (window) history.
		// 'previous_url' => $base_url . '/previous/' . $before;
		$prev_url = 'javascript: history.go(-1)';
		$first_url = $base_url;
	}

	if ( ! empty($after))
	{
		// We're not on the last page
		$next_url = $base_url . '/next/' . $after;
	}

	$link_array['previous_page'][0] = array('previous_url' => $prev_url, 'previous_text' => $prev_text);
	$link_array['first_page'][0] = array('first_url' => $first_url, 'first_text' => $first_text);
	$link_array['next_page'][0] = array('next_url' => $next_url, 'next_text' => $next_text);

	$paging_links = ee()->TMPL->parse_variables($template, array($link_array));

	return $paging_links;
}


/**
 * create and return error message
 *
 * @param string $error_title The title of the error
 * @param string $error_msg The error message
 * @return string
 */
function create_error_msg($error_title = '', $error_msg = '')
{
	// Return error message
$error_tmpl = <<<ERROR_TMPL
<style type="text/css">
	:root, body {
		--ee-panel-bg: #fff;
		--ee-panel-border: #dfe0ef;
		--ee-text-normal: #0d0d19;
		--ee-main-bg: #f7f7fb;
		--ee-link: #5D63F1;
		--ee-link-hover: #171feb;
	}

	*, :after, :before {
		box-sizing: inherit;
	}

	html {
		box-sizing: border-box;
		font-size: 15px;
		height: 100%;
		line-height: 1.15;
	}

	.panel {
		font-family: Roboto,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Ubuntu,"Helvetica Neue",Oxygen,Cantarell,sans-serif;
		font-size: 1rem;
		line-height: 1.6;
		color: var(--ee-text-normal);
		-webkit-font-smoothing: antialiased;
		margin-bottom: 20px;
		background-color: var(--ee-panel-bg);
		border: 1px solid var(--ee-panel-border);
		border-radius: 6px;
	}
	.redirect {
		max-width: 960px;
		min-width: 350px;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%,-50%);
	}

	.panel-heading {
		padding: 20px 25px;
		position: relative;
	}
	.error {
		color: #C00;
	}

	.panel-body {
		padding: 20px 25px;
	}

	.panel-body:after, .panel-body:before {
		content: " ";
		display: table;
	}

	.redirect p {
		margin-bottom: 20px;
	}
	p {
		line-height: 1.6;
	}
	a, blockquote, code, h1, h2, h3, h4, h5, h6, ol, p, pre, ul {
		color: inherit;
		margin: 0;
		padding: 0;
		font-weight: inherit;
	}

	a {
		color: var(--ee-link);
		text-decoration: none;
		-webkit-transition: color .15s ease-in-out;
		-moz-transition: color .15s ease-in-out;
		-o-transition: color .15s ease-in-out;
	}

	a:hover {
		color: var(--ee-link-hover);
	}

	h3 {
		font-size: 1.35em;
		font-weight: 500;
	}

	ol, ul {
		padding-left: 0;
	}

	ol li, ul li {
		list-style-position: inside;
	}
</style>
<section class="flex-wrap">
	<section class="wrap">
		<div class="panel redirect">
			<div class="panel-heading">
				<h3>Facebook Graph API Explorer: <span class="error">Error</span></h3>
			</div>
			<div class="panel-body">
				<p><strong>{$error_title}</strong><p>
				<p>{$error_msg}</p>
			</div>
		</div>
	</section>
</section>
ERROR_TMPL;

	return $error_tmpl;

}

/* End of file */
/* Location: ./system/user/addons/fb_link/helpers/helper_functions.php */
//EOF
