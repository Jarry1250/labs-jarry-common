<?php	
	/**
	 * global.php © 2011
	 * @author Harry Burt <jarry1250@gmail.com>
	 *
	 * @todo reintegrate i18n now we're on Labs
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
	
	echo get_html( 'header', 'Introduction' );
?>
			<p>Welcome to my Toolserver pages. If you have any queries, you can always contact me by leaving a message on <a href="//en.wikipedia.org/wiki/User_talk:Jarry1250">my en.wp talk page</a> or by following that page's "Email this user" link. <em>Note that tools may be periodically removed. If you rely on one that does get removed, just tell me and I will replace it for you.</em> The last such rotation was on 25 February 2011.</p>
		</div>
		<h2 class="screenreaders">Tools</h2>
		<div class="boxes-holder">
			<ul class="boxes">
				<li><h3><a href="/~jarry/svgtranslate/">SVG Translate</a></h3><p>Translate an SVG into a language or your choice.</p></li>
				<li><h3><a href="/~jarry/svgcheck/">SVG Check</a></h3><p>Preview the display of SVG files and detect errors.</p></li>
				<li><h3><a href="/~jarry/templatecount/index.php">Template transclusion counter</a></h3><p>Finds the number of times that a template is transcluded <em>(Wikipedia only)</em>.</li>
			</ul>
<?php
	echo get_html( 'footer' );
?>