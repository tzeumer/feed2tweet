<?php
// Correct timezone
if (!ini_get('date.timezone')) date_default_timezone_set('Europe/Berlin');

// Accounts and feeds to process (as array)
$users = array (
    // Account one
    array(
        'name'      => 'vform',
        'enabled'   => true,
        'api' => array (
            'consumerKey'       => '123',
            'consumerSecret'    => '123',
            'accessToken'       => '123-123',
            'accessTokenSecret' => '123'
        ),
        'feeds' => array(
            array(
                'url'         => 'https://www.tub.tuhh.de/feed/',
                'enabled'     => true,
                'tweetPrefix' => 'Frisch gebloggt: '
            ),
            array(
                'url'         => 'https://tubdok.tub.tuhh.de/feed/rss_2.0/site',
                'enabled'     => true,
                'tweetPrefix' => 'Neu auf tubdok: '
            )
        )
    ),
    //Acount 2
    array(
        'name'      => 'vform2',
        'enabled'   => false,
        'api' => array (
            'consumerKey'       => '123',
            'consumerSecret'    => '123',
            'accessToken'       => '123-123',
            'accessTokenSecret' => '123'
        ),
        'feeds' => array(
            array(
                'url'         => 'https://www.tub.tuhh.de/feed/',
                'enabled'     => true,
                'tweetPrefix' => 'Frisch gebloggt: '
            ),
            array(
                'url'         => 'https://tubdok.tub.tuhh.de/feed/rss_2.0/site',
                'enabled'     => true,
                'tweetPrefix' => 'Neu auf tubdok: '
            )
        )
    )
);

include('config_dev.php');
?>