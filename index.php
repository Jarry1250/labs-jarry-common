<?php	
	/**
	 * global.php � 2011
	 * @author Harry Burt <jarry1250@gmail.com>
	 *
	 * @todo "Introduction" -> i18n
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
	require_once( '/data/project/jarry-common/public_html/global.php' );
	
	echo get_html( 'header', _html( 'homepage-title', 'jarry' ) );
?>
			<p>Welcome to my index of Wikimedia Labs tools I (User:Jarry1250) have been involved in the development of. If you have any queries, you can always contact me by leaving a message on <a href="//en.wikipedia.org/wiki/User_talk:Jarry1250">my en.wp talk page</a> or by following that page's "Email this user" link on the lest hand side. <em>Note that tools may be periodically removed. If you rely on one that does get removed, just tell me and I will replace it for you.</em> The last such rotation was in April 2014, during the transition to Labs.</p>
		</div>
		<h2 class="screenreaders">Tools</h2>
		<div class="boxes-holder">
			<ul class="boxes">
				<li><h3><a href="http://tools.wmflabs.org/wikicup/">WikiCup</a></h3><p>Live scores, plus individual and competition totals.</p><p class="source"><a href="https://github.com/Jarry1250/labs-wikicup"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3><a href="http://tools.wmflabs.org/svgtranslate/">SVG Translate</a></h3><p>Translate an SVG into a language or your choice.</p></li>
				<li><h3><a href="http://tools.wmflabs.org/svgcheck/">SVG Check</a></h3><p>Preview the display of SVG files and detect errors.</p><p class="source"><a href="https://github.com/Jarry1250/labs-svgcheck"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3><a href="http://tools.wmflabs.org/grep/">Grep</a></h3><p>Search page titles on a wiki using regular expressions.</p><p class="source"><a href="https://github.com/Jarry1250/labs-grep"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3><a href="http://tools.wmflabs.org/bytesadded/">BytesAdded</a></h3><p>Show net and absolute bytes added by an autor (en.wp article space only).</p><p class="source"><a href="https://github.com/Jarry1250/labs-bytesadded"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3><a href="http://tools.wmflabs.org/wmukevents/">Wikimedia UK events</a></h3><p>Auto-generated .ics (calendar) file</p><p class="source"><a href="https://github.com/Jarry1250/labs-wmukevents"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3><a href="http://tools.wmflabs.org/signpost/">Signpost publishing</a></h3><p>Tools built to assist the Signpost's Editor-in-Chief</p><p class="source"><a href="https://github.com/Jarry1250/labs-signpost"><?= _html( 'view-source' , 'general' ); ?></p></li>
				<li><h3>Scripts</h3><p>Scripts underlying other tasks performed by my bots</p><p class="source"><a href="https://github.com/Jarry1250/labs-jarry-common"><?= _html( 'view-source' , 'general' ); ?></p></li>
			</ul>
<?php
	echo get_html( 'footer' );