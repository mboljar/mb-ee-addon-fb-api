# Facebook Graph API Explorer

Works only for Facebook Pages and Instagram Business/Creator accounts, not for personal profiles.

Facebook and Instagram have nice widgets to embed in your website. And in many occasions the recommended way to add Facebook and/or Instagram to your website. But those widgets display limited data and are very hard to customize.

What if you want to display some specific data those widgets do not provide? Or you want to add your own visual style to the output? Enter the Facebook Graph API Explorer for ExpressionEngine.

With this add-on you can create custom Facebook Graph API and Instagram Graph API queries to get the data _you_ want and style the output the way _you_ want.

## Requirements

* ExpressionEngine 3+. (Tested on EE 3.5.17, EE 4.3.8, EE 5.4.0 and EE 6.x).
* an SSL (HTTPS) enabled website. Facebook requires HTTPS for authentication.
* a Facebook Page (not a personal profile).
* a [Facebook Developer account](https://developers.facebook.com/) and a Facebook App.
* Facebook App ID and Facebook App Secret, which you will get after creating an app in the Facebook Developer portal.
* `Facebook Login` installed in the Facebook App Dashboard with:
  * `Client OAuth Login` enabled
  * `Web OAuth Login` enabled
* For Instagram:
  * an Instagram Business account or an Instagram Creator account.
  * the `Instagram Graph API` installed in the Facebook App Dashboard.
* The correct permissions (_You may have to apply for App Review_):
  * For a Facebook page: `manage_pages`, `pages_show_list`
  * For Instagram: `instagram_basic`
* Website domain(s) added to:
  * `Facebook App Dashboard -> Settings -> Advanced -> Domain Manager`
  * `Facebook App Dashboard -> Facebook Login -> Settings-> Valid OAuth Redirect URIs`

## Installation

* Download the add-on from GitHub and unzip the file to a folder of your choice
* Copy `fb_graph_api/fb_graph_api` folder to `./system/user/addons/fb_graph_api`
* Go to the add-ons page in your control panel and install the add-on

## Updating

* Copy `fb_graph_api/fb_graph_api` folder to `./system/user/addons/fb_graph_api` replacing the files in the destination folder
* Go to the add-ons page in your control panel and use the update button

## Facebook App Settings
* Go to the Facebook App Settings page of the add-on and fill in the `App ID` and the `App Secret` you obtained from your [Facebook Developer app dashboard](https://developers.facebook.com/apps)
* Click the `Save Settings` button
* After saving the settings click the `Get Access Tokens` button. A list of pages you manage will appear
* Select the token of the Facebook Page you want to query and click the `Save Token` button
* The Facebook Graph API Explorer add-on is now installed and activated.

Optionally you can change your preferred Facebook Graph version. Some defaults are already set. If you need an earlier or later version than the preset versions, you need to change the `Minimum Facebook Graph version` and/or the `Maximum Facebook Graph version` settings first. After saving those settings the `Facebook Graph version selector` will be updated for you to select another version and to save it again.

## Developer Settings

##### Show output error messages

Displays output error messages.
Best to uncheck for production websites.

##### Pretty-print JSON

It makes JSON output more readable.
Uncheck for production websites.

##### Show Node Metadata

Discover supported edges for your node.
Uncheck for production websites.

#### Example getting metadata

Check the `Show Node Metadata` setting and save. It is recommended to check the `Pretty-print JSON` setting too.

Create a template with the following tag:

```
{exp:fb_graph_api:get node="[NODE]" json="true"}
```
Run the template in your browser and a list of available fields and edges (connections) of your node will be displayed in JSON format.
[NODE] can be:

* Your Facebook Page ID
* Your Facebook Page alias (vanity)
* Your Instagram Business account ID
* Your Instagram Creator account ID

## Usage

### `{exp:fb_graph_api:get}`

#### Example Usage Facebook

```
{exp:fb_graph_api:get node="[FACEBOOK PAGE ID or FACEBOOK PAGE ALIAS]" edge="posts" fields="message,full_picture,permalink_url" limit=5}
  {if full_picture}<img src="full_picture">{/if}
  {message}
  {permalink_url}
{/exp:fb_graph_api:get}
```

#### Example Usage Instagram

```
{exp:fb_graph_api:get node="[INSTAGRAM BUSINESS or CREATOR ACCOUNT ID]" edge="media" fields="id,timestamp,caption,media_url,permalink,like_count,comments_count" limit="12"}
	{id}
	{timestamp}
	{caption}
	{media_url}
	{permalink}
	{like_count}
	{comments_count}
{/exp:fb_graph_api:get}
```

#### Parameters

##### node (*required*)

[Facebook Page ID | Facebook Page Alias | Instagram Business Account ID | Instagram Creator Account ID]

Examples for the [ExpressionEngine Facebook page](https://www.facebook.com/expressionengine/):

`{exp:fb_graph_api:get node="expressionengine"}`

`{exp:fb_graph_api:get node="359401999932"}`

##### edge

[name of the edge]

Browse the [Facebook Graph API documentation][Facebook Graph API] and [Instagram Graph API documentation][Instagram Graph API]  for available edges.

Examples of getting events by using the events edge:

`{exp:fb_graph_api:get node="[NODE]" edge="events"}`

##### fields

[comma-separated list of fieldnames]

Browse the [Facebook Graph API documentation][Facebook Graph API] and [Instagram Graph API documentation][Instagram Graph API]  for available fields.

Examples for getting specific event fields:

```
{exp:fb_graph_api:get node="[NODE]" edge="events" fields="id, name, description, cover{source}"}
	{id}
	{name}
	{description}
	{cover}
		{cover:source}
	{/cover}
{/exp:fb_graph_api:get}
```

_Notice the selection of the subfield `source` of the `cover` field and the way to display the selected subfield._

##### include_canceled

[true]

_Facebook Page only. Does not work for Instagram._
_Only works for the `events` edge._

This is an optional parameter but if you want to display canceled events it is required and must be set to **true**. Facebook omits canceled events by default.

`{exp:fb_graph_api:get node="[NODE]" edge="events" fields="id, name, description, is_canceled" include_canceled="true"}`

##### since

[date in YYYY-MM-DD or UNIX timestamp format]

_Facebook Page only. Does not work for Instagram._

To narrow down the query to start from a specific date, enter a date in `YYYY-MM-DD` format or a 10-digit UNIX timestamp.

Example with YYYY-MM-DD format:
`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" since="2021-01-01"}`

Example with 10-digit UNIX timestamp format:
`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" since="1609459200"}`

##### until

[date in YYYY-MM-DD or UNIX timestamp format]

_Facebook Page only. Does not work for Instagram._

To narrow down the query to end at a specific date, enter a date in `YYYY-MM-DD` format or a 10-digit UNIX timestamp.

Example with YYYY-MM-DD format:
`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" until="2021-01-01"}`

Example with 10-digit UNIX timestamp format:
`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" until="1609459200"}`

##### sort

[fieldname to sort the results]

_The official Facebook Graph API doesn't support sorting for most edges._
_The `Facebook Page events` edge seems to be one of the view that does, if not the only one..._
_Facebook automatically orders ascending._

The fieldname to sort the query.

`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" sort="[FIELDNAME]"}`

##### time_filter

[upcoming | past]

_Use with Facebook Page Events edge to filter the results._

Available filters: _upcoming_ and _past_.

`{exp:fb_graph_api:get node="[NODE]" edge="events" fields="[FIELDNAMES]" sort="[FIELDNAME]" time_filter="past"}`

##### limit

[integer number]

To limit the amount of items returned, enter an integer.

`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" limit="10"}`

##### paging

[bottom | top | both]

For creating "previous", "first" and "next" page links.
For placement/positioning the links `bottom`, `top` and `both` are supported. Defaults to `bottom`.

Must be used in conjuction with `{paging}` tag pair.
```
{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" paging="[bottom|top|both]"}

	{!-- your data variables --}

		{paging}
			{previous_page}
				{if previous_url}
					<a href="{previous_url}">{previous_text}</a>
				{if:else}
					{previous_text}
				{/if}
			{/previous_page}

			{first_page}
				{if first_url}
					<a href="{first_url}">{first_text}</a>
				{if:else}
					{first_text}
				{/if}
			{/first_page}

			{next_page}
				{if next_url}
					<a href="{next_url}">{next_text}</a>
				{if:else}
					{next_text}
				{/if}
			{/next_page}
		{/paging}

{/exp:fb_graph_api:get}
```

You can put the `{paging}` tag pair anywhere _within_ the main tag. It doesn't matter where, the add-on will place it where you assign it.

The paging output variables are:

* `{previous_url}`,
* `{previous_text}` (optional, defaults to 'previous'),
* `{first_url}`,
* `{first_text}` (optional, defaults to 'first'),
* `{next_url}`,
* `{next_text}` (optional, defaults to 'next').

Instead of the `*_text` variables you can use your own text in the template.

**Note:** only use the `{paging}` tag pair once, otherwise you get duplicate paging links!

##### json

[true]

Set to `true`. Disabled by default.

Returns pure JSON. All other parsing is omitted. Great when you want to use it in a JavaScript driven app i.e. a React.js app.

`{exp:fb_graph_api:get node="[NODE]" edge="[EDGE]" fields="[FIELDNAMES]" json="true"}`

#### Variables

The variables the main tag returns match the fieldnames you select in the fields parameter.

## Changelog

### 1.1.4

* Removed constants `MIN_GRAPH_VERSION` and `MAX_GRAPH_VERSION` from `config.php`
* Added minimum and maximum Facebook Graph version settings to the Facebook App Settings page.

### 1.1.3

* Added constants `MIN_GRAPH_VERSION` and `MAX_GRAPH_VERSION` to `config.php` for use in `app_settings.php`

### 1.1.2

* Added Facebook Graph version selector to the Facebook App Settings page
* Removed constant `FACEBOOK_GRAPH_VERSION` from `config.php`
* Removed `order` parameter, since Facebook doesn't seem to support it anymore. When `sort='fieldname'` is used, Facebook will automatically order ascending (for applicable fields)
* Added `time_filter` parameter for (Page Events)

### 1.1.1

* Fixed bug for EE3, EE4 and EE5 where `$sidebar->addDivider();` is not supported
* Added some strings to 'fb_graph_api_lang.php' to replace some leftover hardcoded language strings
* Added constant `FB_GRAPH_API_MOD_VER` to `config.php`
* Renamed constant `FACEBOOK_GRAPH_API_NAME` to `FB_GRAPH_API_MOD_NAME` in `config.php`

### 1.1.0

* Added cursor-based browsing/paging links
* Added `paging` parameter
* Renamed parameter `node_id` to `node` (can't seem to make up my mind)
* Added developer settings (error messages, pretty-print JSON, JSON Node metadata)
  * Added database table for Developer Settings
  * Created separate pages for Facebook App Settings and Developer Settings in the control panel
* Cleaned up the code as per ExpressionEngine guidelines

### 1.0.2

* Fixed initial install error notices where an undefined array was referenced
* Restructured the repo and rewritten readme.md as per ExpressionEngine guidelines
* Removed `{data}{/data}` tag pair from the output. The `data` item remains as top level key in JSON output

### 1.0.1

- Renamed constant `GRAPH_VERSION` to `FACEBOOK_GRAPH_VERSION` to avoid conflict with Ron Hickson's fb_link add-on
- Renamed `graph()` function to `get()`
- Renamed parameter `user_id` to `node_id`
- Updated readme.md
- Fixed pure JSON output
- Fixed typo in class name
- Ensured backward compatibility

### 1.0.0

- Initial release

## Reference

- [Facebook Graph API](https://developers.facebook.com/docs/graph-api/using-graph-api)
- [Facebook Graph API Page node reference](https://developers.facebook.com/docs/graph-api/reference/page/)
- [Instagram Graph API](https://developers.facebook.com/docs/instagram-api)

## Attribution

##### Code
This add-on is built on Ron Hickson's Facebook Link add-on and the base code was completely written by him.
I merely adapted the code to play nice with EE3-EE6 and make it more user-friendly by adding parameters and settings.

Without Ron Hickson this add-on would probably never have come to life.

Ron's Facebook Link add-on can be found at:

- Devot-ee: [https://devot-ee.com/add-ons/facebook-link](https://devot-ee.com/add-ons/facebook-link)
- GitHub: [https://github.com/rhgarage/fb_link](https://github.com/rhgarage/fb_link)
- EE-Forge: [https://ee-forge.com/add-ons/facebook_link](https://ee-forge.com/add-ons/facebook_link)

##### Control panel icon

The icon used in the control panel is [_social media scanner_](https://thenounproject.com/term/social-media-scanner/1954298/) by [_WEBTECHOPS LLP_](https://thenounproject.com/creativepriyanka/) from the [Noun Project](https://thenounproject.com/)
