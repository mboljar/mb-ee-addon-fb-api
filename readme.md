# Facebook Graph API Explorer 1.0.1
Facebook and Instagram feeds on your website the way _you_ want it!

Works only for Facebook Pages and Instagram Business/Creator accounts, not for personal profiles.

Facebook and Instagram have nice widgets to embed in your website. And in many occasions the recommended way to add Facebook and/or Instagram to your website. But those widgets display limited data and are very hard to customize.

What if you want to display some specific data those widgets do not provide, or you want to add your own visual style to the output? Enter the Facebook Graph API Explorer for ExpressionEngine.

With this add-on you can create custom Facebook Graph API queries to get the data you want and the data will be returned raw. You have to apply your own styling to it so it will seamlessly fit into your website.

---

## License

MIT License

---

## Requirements

* ExpressionEngine 6. Not tested on lower versions (yet).
* a Facebook Developer account and a Facebook App (https://developers.facebook.com/docs/development/)
* Facebook App ID and Facebook App Secret which you will get after creating an app in the Facebook Developer portal.
* Facebook Login installed in the Facebook App Dashboard.
* a Facebook Page (not a personal profile).
* For Instagram:
  * an Instagram Business account or an Instagram Creator account.
  * the Instagram Graph API installed in the Facebook App Dashboard.
* The correct permissions (_You may have to apply for App Review_):
  * For a Facebook page at least: `manage_pages`, `pages_show_list`
  * For Instagram: `instagram_basic`
* Website domain(s) added to:
  * `App Dashboard -> Settings -> Domain Manager`
  * `App Dashboard -> Facebook Login -> Settings-> Valid OAuth Redirect URIs`

---

## Installation and activation

* Download the add-on from Github and unzip the file to destination of your choice
* Rename the unzipped folder `fb_graph_api-1.0.1` to `fb_graph_api`
* Copy the `fb_graph_api` folder to `./system/user/addons/fb_graph_api`
* Go to the add-ons page in your control panel and install the add-on
* Go to the settings page of the add-on and fill in the form (App ID and App Secret) and click the `Save Settings` button.
* After saving the settings click the `Get Access Tokens` button. A list of pages you manage will display
* Select the Facebook page you want to query and click the `Save Token` button
* The Facebook Graph API Explorer add-on is now installed and activated

![Facebook Explorer Settings](D:\Github\fb_graph_api\Facebook-Graph-API-Explorer.png)

---

## Tagpair

```
{exp:fb_graph_api:get}
	output variables
{/exp:fb_graph_api:get}
```

**Returned variables match fieldnames.**

---

## Parameters

### node_id="node id" (required)
[Facebook Page ID | Facebook Page Alias | Instagram Business Account ID | Creator Account ID]

Examples for the [ExpressionEngine Facebook page](https://www.facebook.com/expressionengine/):

```
{exp:fb_graph_api:get node_id="expressionengine"}
	output variables
{/exp:fb_graph_api:get}`
```

```
{exp:fb_graph_api:get node_id="359401999932"}
	output variables
{/exp:fb_graph_api:get}
```



### edge="edge name"(optional)
Browse the [Graph API documentation][api docs] for available edges.

Examples of getting events by using the events edge:

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events"}
	output variables
{/exp:fb_graph_api:get}
```



### fields="comma-separated list of fieldnames" (optional)
Browse the [Graph API documentation][api docs] for available fields.

Examples for getting specific event fields:

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events" fields="id, name, start_time, end_time, description, cover{source}"}
	{id}
	{name}
	{start_time}
	{end_time}
	{description}
	{cover}
		{cover:source}
	{/cover}
{/exp:fb_graph_api:get}
```

_Notice the selection of the subfield `source` of the `cover` field and the way to display the selected subfield._



### include_canceled="true" (optional)

Facebook Page only. Does not work for Instagram.

This is an optional parameter but if you want to display canceled events it is required.
Facebook omits canceled events by default.

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events" fields="id, name, start_time, end_time, description, is_canceled" include_canceled="true"}
	{id}
	{name}
	{start_time}
	{end_time}
	{description}
	{is_canceled}
{/exp:fb_graph_api:get}
```



### since="yyyy-mm-dd" (optional), until="yyyy-mm-dd" (optional)
Facebook Page only. Does not work for Instagram.

To narrow down the query to a specific timeframe. These parameters can be used standalone as well as combined.  
Standard date/time formatting can be applied to date and time variables.

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events" fields="id, name, start_time, end_time, description" since="2020-12-01" until="2020-12-31"}
	{id}
	{name}
	{start_time}
	{end_time}
	{description}
{/exp:fb_graph_api:get}
```

Displaying events in December 2020.



### sort="fieldname" (optional), order="[asc | desc]" (optional)

Facebook Page only. Does not work for Instagram.

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events" fields="id, name, start_time, end_time, description" sort="start_time" order="asc"}
	{id}
	{name}
	{start_time}
	{end_time}
	{description}
{/exp:fb_graph_api:get}
```

Sorting by field `start_time` and ordering ascending.



### json="true" (optional)

Returns the query in pure JSON format. All other parsing will be lost.

```
{exp:fb_graph_api:get node_id="[YOUR NODE ID]" edge="events" fields="id, name, start_time, end_time, description" json="true"}
	json formatted output
{/exp:fb_graph_api:get}
```

---

## Instagram example

```
{exp:fb_graph_api:get node_id="YOUR INSTAGRAM BUSINESS/CREATOR ACCOUNT ID" edge="media" fields="id,timestamp,caption,media_url,permalink,like_count,comments_count" limit="12"}
	{id}
	{timestamp}
	{caption}
	{media_url}
	{permalink}
	{like_count}
	{comments_count}
{/exp:fb_graph_api:get}
```

**Note:** Instagram media edge doesn't support `include_canceled`, `since`, `until`, `sort` and `order` parameters.

## Acknowledgement

This add-on is built on Ron Hickson's Facebook Link add-on.  
99% of the code was written by him. I merely adapted the code to play nice with EE6 and to better fit my needs.

Long story short, without Ron Hickson this add-on would probably never have come to life.

Ron's Facebook Link add-on can be found at:

* Devot-ee: [https://devot-ee.com/add-ons/facebook-link](https://devot-ee.com/add-ons/facebook-link)
* Github: [https://github.com/rhgarage/fb_link](https://github.com/rhgarage/fb_link)
* His own website: [https://ee-forge.com/add-ons/facebook_link](https://ee-forge.com/add-ons/facebook_link)

---

## Useful links

* [Facebook Graph API docs](https://developers.facebook.com/docs/graph-api)
* [Using the Graph API][api docs]
* [Facebook Graph API Explorer Tool](https://developers.facebook.com/tools/explorer/)
* [Instagram Graph API docs](https://developers.facebook.com/docs/instagram-api)

---

## Changelog

**1.0.1** (2021-02-25)

* Renamed constant GRAPH_VERSION to FACEBOOK_GRAPH_VERSION to avoid conflict with Ron Hickson's fb_link add-on
* Renamed graph() function to get()
* Renamed parameter user_id to target_id
* And then renamed parameter target_id to node_id  :confused:
* Updated readme.md
* Fixed pure JSON output
* Fixed typo in class name
* Started to work on backward compatibility



**1.0.0** (2021-02-25)

* Initial release



[api docs]: https://developers.facebook.com/docs/graph-api/using-graph-api
