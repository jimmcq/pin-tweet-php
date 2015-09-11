This code will allow a pinball machine (that meets the requirements below) to automatically post scores to Twitter.

REQUIREMENT 1 - A Stern pinball machine based on the S.A.M. board system.  You can find a list here: http://www.ipdb.org/search.pl?searchtype=advanced&mpu=54

REQUIREMENT 2 - The pinball machine's firmware must be updated to include the Communication Patch.  You'll
need the Pinball-Browser app available from http://tiny.cc/pinballbrowser and the guide at
https://pinside.com/pinball/forum/topic/acdc-display-and-modify-dot-matrix-images/page/25#post-1540898

REQUIREMENT 3 - A USB to RS232 Null Modem cable such as this http://www.amazon.com/StarTech-com-Modem-Serial-Adapter-ICUSB232FTN/dp/B008634VJY/

REQUIREMENT 4 - An internet connected computer that can run PHP scripts.  A $35 Raspberry Pi will work.  You'll
need the php5-cli and php5-curl packages installed.

Make sure to clone this repo with its submodule:
git clone --recursive https://github.com/jmcquillan/pin-tweet-php

You will need to copy config-sample.json to config.json and edit its contents.  You will need the Keys and Access
Tokens for the Twitter account you want to post to.

You could use a tool such as Supervisor http://supervisord.org/ to start the script when the computer boots, and to
make sure it keeps running.
