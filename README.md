# Sample Ingot Test Type
<strong>This is a guide for developers -- Ingot doesn't require any PHP knowledge to use. This sample plugin is to help developers create their own integrations.</strong>

Ingot is the simplest tool for A/B testing with WordPress. Learn more at [IngotHQ.com](http://ingothq.com). If you would like to discuss custom integrations for your site, or your WordPress plugin or theme, email Josh (Josh@JoshPress.net) or DM him on WordPress slack (Shelob9).
 
 
### What This Does
As is, this is a plugin, that creates a custom Ingot click test that shows a a variety of images -- in this case cute cat photos. It's starts the test when a user goes to the front-page and it tracks a conversion when the reach a page with the ID of 42. It provides a function for displaying the chosen cute cat photo.

The point of this is to show you how to make your own custom tests for Ingot. It shows you how to create a test programatically, add variants to it, and show that test in the front-end. 

This example plugin is super documented inline. Please read the three files and pay special attention to wherever you see a @TODO

Please pay attention to the usage of `ingot_is_bot()` which is used to prevent tracking bots. We need to avoid messing up our numbers by tracking bots, but we still have to show some content so Google and such are happy and if a human gets considered to be a bot, they don't get a bad experience.

### Tracking A Conversion Via AJAX
In this example conversions are tracked by reaching template_redirect with a specific post ID. You could hook that to any hook. Or you can use an AJAX call to register the conversion.


```
<script>
jQuery( document ).ready( function ( $ ) {
    /**
     * Setup our variables -- these are localized by Ingot
     */
    var session_id = INGOT_UI.session.ID;
    var session_nonce = INGOT_UI.session_nonce;
    var nonce = INGOT_UI.nonce;
    var api_url = INGOT_UI.api_url;


    /**
     * Make a function that sends conversion
     *
     * @TODO You will need to bind this to an event
     */
    function send_conversion(){
        //@TODO you will need to set variant ID from somewhere.
        //with default Ingot tests we put this as an attribute of the element we are targetting
        var id = 0;

        //setup data to send
        var data = {
            id: id,
            ingot_session_nonce: session_nonce,
            ingot_session_ID: session_id
        };

        //BTW setting up GET & POST variables seams redundant, but Ingot tracks the GET vars for session tracking earlier than REST API runs.
        var url = api_url + 'variants/' + id + '/conversion?_wpnonce=' + nonce + '&ingot_session_nonce=' + session_nonce + '&ingot_session_ID=' + session_id;

        //send conversion
        $.ajax({
            url: url,
            method: "POST",
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', nonce );
            }
        }).success(function( data, textStatus, jqXHR ) {

        } ).error( function(){

        } ).fail( function()  {

        });

    }
});
</script>
```

### Copyright & License
Copyright 2015 Ingot LLC and license under the terms of the GNU GPL v2+ so you must to:)
