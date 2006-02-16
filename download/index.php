<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/*!
	@header Download Document
	@discussion Downloads a song to the user, if they have download permission.
	Special thanks to the Horde project for their Browser class that makes this so easy.
*/

require('../modules/init.php');
require(conf('prefix') . '/modules/horde/Browser.php');

$browser = new Browser();

/* If we are running a demo, quick while you still can! */
if (conf('demo_mode') || !$GLOBALS['user']->has_access('25') || !$GLOBALS['user']->prefs['download']) {
	access_denied();
}

/*
   If they are using access lists let's make sure
   that they have enough access to play this mojo
*/
if (conf('access_control')) {
        $access = new Access(0);
        if (!$access->check('50', $_SERVER['REMOTE_ADDR'])) {
                debug_event('access_denied', "Download Access Denied, " . $_SERVER['REMOTE_ADDR'] . " does not have download level",'3');
                access_denied();
        }
} // access_control is enabled

/* Check for a song id */
if (!$_REQUEST['song_id']) { 
	echo "Error: No Song found, download failed";
	debug_event('download','No Song found, download failed','2');
}

/* If we're got require_session check for a valid session */
if (conf('require_session')) { 
	if (!session_exists(scrub_in($_REQUEST['sid']))) { 
		die(_("Session Expired: please log in again at") . " " . conf('web_path') . "/login.php");
		debug_event('session_expired',"Download Access Denied: " . $GLOBALS['user']->username . "'s session has expired",'3');
	}
} // if require_session
	

/* If the request is to download it... why is this here? */
if ($_REQUEST['action'] == 'download') {
	$song = new Song($_REQUEST['song_id']);
	$song->format_song();
	$song->format_type();
	$song_name = str_replace('"'," ",$song->f_artist_full . " - " . $song->title . "." . $song->type);
	// Use Horde's Browser class to send the right headers for different browsers
	// Should get the mime-type from the song rather than hard-coding it.
	header("Content-Length: " . $song->size);
	$browser->downloadHeaders($song_name, $song->mime, false, $song->size);
	$fp = fopen($song->file, 'r');
	fpassthru($fp);
	fclose($fp);
}

?>
