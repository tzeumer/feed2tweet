<?php
/**
 * @brief   Just a proof of concept hack for generating tweets from feed entries
 *
 * @todo (ideas)
 * - Generate images from (full) feed title (or full content) and attach
 * - First run should be without tweeting, because otherwise the feed would be
 *   tweeted. We only know the "latest already tweeted after the first run"
 *   => probably use $feed->items[$id]->getPublishedDate() to set some limit foreach
 *      the first run
 *
 * @notes
 * - Really not for productive use ;)
 *
 * @author Tobias Zeumer <github@verweisungsform.de>
 */
header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/../../vendor/autoload.php';


require('config.php');
// Process each user
foreach ($users as $id => $user) {
    if ($user['enabled'] == true) {
        // Process all feeds
        foreach ($user['feeds'] as $feedCfg) {
            if (!$feedCfg['enabled']) continue; // Feed disabled in config.php
            $feed = getFeed($feedCfg['url']);            
            if (!$feed) continue;               // No entries (or unmodified)

            $dbg = '<h1>'.$feed->getTitle().'</h1>';
            // Check each feed item for... some criteria
            // @see     https://github.com/fguillot/picoFeed/blob/master/docs/feed-parsing.markdown
            foreach ($feed->getItems() as $id => $value) {
                $tweet = $feedCfg['tweetPrefix'].$feed->items[$id]->getTitle().' ';
                /* A URL of any length will be altered to 23 characters, even if 
                the link itself is less than 23 characters long. Your character 
                count will reflect this. 
                So maximum tweet lenght is 116 (EXCLUDING a whitespace before the url)
                */
                if (strlen($tweet) <= 116) {
                    $tweet .= $feed->items[$id]->getUrl();
                }
                else {
                    $tweet = substr($tweet, 0, 113);
                    $tweet = substr($tweet, 0, strrpos($tweet, ' ')).'... '.$feed->items[$id]->getUrl();
                }
                $dbg .= "<p>$tweet</p>";
                tweet($user['api'], $tweet);
            }
            //echo $dbg;
        }
    }
}


/**
 * @brief   Use https://github.com/jublonet/codebird-php for tweeting
 *
 * @param $api      (array) @see config.php
 * @param $message  (string) The tweet
 * @param $iamge    (string) Path to an image to attach
 * @return void
 */
function tweet($api, $message, $image = '') {
    // note: consumerKey, consumerSecret, accessToken, and accessTokenSecret all come from your twitter app at https://apps.twitter.com/
    \Codebird\Codebird::setConsumerKey($api['consumerKey'], $api['consumerSecret']);
    $cb = \Codebird\Codebird::getInstance();
    $cb->setToken($api['accessToken'], $api['accessTokenSecret']);

    //build an array of images to send to twitter
    if ($image) {
        $reply = $cb->media_upload(array(
            'media' => $image
        ));
        //upload the file to your twitter account
        $mediaID = $reply->media_id_string;

        //build the data needed to send to twitter, including the tweet and the image id
        $params = array(
            'status' => $message,
            'media_ids' => $mediaID
        );
    } else {
        //build the data needed to send to twitter, including the tweet and the image id
        $params = array(
            'status' => $message
        );
    }

    //post the tweet with codebird
    $cb->statuses_update($params);
}


/**
 * @brief   Use https://github.com/fguillot/picoFeed to fetch feeds
 *
 * @param $url      (string) Url of the feed
 * @return (object) $feed or error message
 */
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;
function getFeed($url) {
    try {
        $reader = new Reader;

        // Fetch from your database the previous values of the Etag and LastModified headers
        // @note $etag and $last_modified aren't really necessary, because a feed
        // might be modified, but we still would not want the already tweeted posts
        $etag = $last_modified = $last_id = '';
        $filename = parse_url($url, PHP_URL_HOST).md5($url).'.txt';
        if (file_exists("./cache/$filename")) {
            $last_check = explode(';', file_get_contents("./cache/$filename"));
            $etag = $last_check[0];
            $last_modified = $last_check[1];
            $last_id = $last_check[2];
        }

        // Return a resource
        $resource = $reader->download($url, $last_modified, $etag);

        // Return true if the remote content has changed
        if ($resource->isModified()) {
            // Return the right parser instance according to the feed format
            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            // Return a Feed object
            $feed = $parser->execute();
            
            // Store the Etag and the LastModified headers it for the next requests
            $etag = $resource->getEtag();
            $last_modified = $resource->getLastModified();
            $current_id = $feed->items[0]->getId();
            file_put_contents("./cache/$filename", "$etag;$last_modified;$current_id");

            // Print the feed properties with the magic method __toString()
            // echo $feed;

            // Unset already tweeted posts
            $found = false;
            foreach ($feed->items as $id => $value) {
                if ($feed->items[$id]->getId() == $last_id || $found == true) {
                    unset($feed->items[$id]);
                    $found = true;
                }
            }

            // return feed
            return $feed;
        }
        else {
            return array();
        }
    }
    catch (PicoFeedException $e) {
        return $e;
    }
}


?>