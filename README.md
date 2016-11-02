This is just a proof of concept on how to get a working tool that can tweet feed entries.

It depends heavily on these two libraries
# https://github.com/fguillot/picoFeed
# https://github.com/jublonet/codebird-php

Quickstart
# Clone repository or download zip file
#* cd .../feed2tweet
#* composer install (or composer update)
# Create a Twitter App for each twitter account to tweet to (https://apps.twitter.com/app/new)
#* Clueless? Helps: https://iag.me/socialmedia/how-to-create-a-twitter-app-in-8-easy-steps/
# Edit src/feed2tweet/config.php (your accounts and the feeds)
# Open src/feed2tweet/index.php via cron or something like that...