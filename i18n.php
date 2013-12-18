<?php
/**
 * Personal internationalisation © 2011 - 2013
 * @author Harry Burt <jarry1250@gmail.com>
 * @package Jarry
 *  
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with This program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

require_once( '/home/project/intuition/src/Intuition/ToolStart.php' );
$I18N = new TsIntuition( array(
	'domain' => 'jarry',
) );

//Request submitted for

//Requests submitted for (stale)
$I18N->setMsg('options', 'Options', 'general');
$I18N->setMsg('error', 'Error', 'general');
$I18N->setMsg('warning', 'Warning', 'general');

//Not yet requested
$I18N->setMsg('error-filename', 'There was a problem with your choice of filename. Either it does not fit the correct pattern ("File:Example.svg") or a file already exists at that filename.', 'svgtranslate');
$I18N->setMsg('error-licensing', 'There was a problem ascertaining the license of the source SVG file from the file description page. This may be due to the lack of a recognised license or the presence of a deletion template.', 'svgtranslate');
$I18N->setMsg('description-edit', 'edit the description page', 'svgtranslate');
$I18N->setMsg('error-status', 'Wikimedia Labs is experiencing problems at the moment.', 'jarry');
$I18N->setMsg('error-server-status', 'Database cluster(s) $1 is/are experiencing problems at the moment.', 'jarry');
$I18N->setMsg('error-server-lag', 'Database cluster(s) $1 is/are experiencing significant lags of more than five minutes at the moment.', 'jarry');
$I18N->setMsg('error-output-impaired', 'Depending on what action you are trying to perform, this may negatively affect the accuracy of any output you receive.', 'jarry');
$I18N->setMsg('server-desc-s1', 'Issues with S1 affect data collected from the English Wikipedia.', 'jarry');
$I18N->setMsg('server-desc-s2', 'Issues with S2 affect data collected from a number of medium-sized Wikipedias, as well as the English Wikiquote and Wiktionary.', 'jarry');
$I18N->setMsg('server-desc-s3', 'Issues with S3 affect data collected from a large number of smaller projects (not just Wikipedias).', 'jarry');
$I18N->setMsg('server-desc-s4', 'Issues with S4 affect data collected from Wikimedia Commons.', 'jarry');
$I18N->setMsg('server-desc-s5', 'Issues with S5 affect data collected from the German Wikipedia.', 'jarry');
$I18N->setMsg('server-desc-s6', 'Issues with S6 affect data collected from the French, Japanese and Russian Wikipedias.', 'jarry');
$I18N->setMsg('server-desc-s7', 'Issues with S7 affect data collected from a number of medium-sized Wikipedias, as well as Meta.', 'jarry');
$I18N->setMsg('error-lag-duration', 'Data from this cluster is currently lagged by $1 seconds.', 'jarry');
$I18N->setMsg('error-current-status', 'Its current status is $1.', 'jarry');
$I18N->setMsg('forking', 'detailed forking instructions', 'jarry');
$I18N->setMsg('namespace-main', '(main)', 'jarry');

function to_utf8( $string ) {
	// From http://w3.org/International/questions/qa-forms-utf-8.html
    if ( preg_match('%^(?:
      [\x09\x0A\x0D\x20-\x7E]            # ASCII
    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
)*$%xs', $string) ) {
        return $string;
    } else {
        return iconv( 'CP1252', 'UTF-8', $string);
    }
} 