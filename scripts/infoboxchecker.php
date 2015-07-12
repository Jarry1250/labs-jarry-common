<?php
	/**
	 * infoboxchecker.php (c)2011-15
	 * @author Harry Burt <jarry1250@gmail.com>
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
	if( $_POST ){
		$lang = htmlspecialchars( $_POST["lang"] );
		$template = htmlspecialchars( $_POST["template"] );
		$category = htmlspecialchars( $_POST["category"] );
		$inverse = htmlspecialchars( $_POST["inverse"] );
		if( $inverse == "true" ){
			$inverse = "&inverse=true";
		}
		header( "Location: http://tools.wmflabs.org/jarry-common/scripts/infoboxchecker.php?lang=$lang&template=$template&category=$category$inverse" );
	}

	require_once( '/data/project/jarry-common/public_html/global.php' );

	// connect to database
	require_once( '/data/project/jarry-common/public_html/libs/database.php' );
	if( isset( $_GET['lang'] ) ){
		$lang = $_GET["lang"];
		$template = str_replace( " ", "_", $_GET["template"] );
		$category = str_replace( " ", "_", $_GET["category"] );
		if( $_GET["inverse"] != "true" ){
			$mod = " NOT";
		}
		$database = dbconnect( $lang . "wiki-p" );
		$tagged = array();
		$result = $database->query( "SELECT p1.page_title FROM templatelinks JOIN page AS p1 ON tl_from=p1.page_id JOIN page AS p2 ON p1.page_title=p2.page_title LEFT JOIN categorylinks ON cl_from=p2.page_id AND cl_to='$category' WHERE p1.page_namespace=0 AND p2.page_namespace=1 AND tl_namespace=10 AND tl_title='$template' AND cl_to IS$mod NULL;" );
		while( $row = $result->fetch_assoc() ){
			array_push( $tagged, str_replace( "_", " ", $row["page_title"] ) );
		}
		sort( $tagged );
		$count = count( $tagged );
	}
	echo get_html( 'header', 'Infobox Existence Checker' );
?>
<script type="text/javascript">
	function sub() {
		if ( document.forms[0].elements["lang"].value == "" || document.forms[0].elements["category"].value == "" || document.forms[0].elements["template"].value == "" ) {
			alert( "Please enter a required value." );
			return false;
		}
	}
</script>
<p>Though this tool is designed for discovering situations where an article transcludes an infobox but still has its
	talk page tagged as requiring one, it can be used successfully for any 'Main article transcludes X and talk page is
	in category Y type requests'. It was written after a request by en Wikipedia user PC78 and the SQL was written by
	toolserver admins flyingparchment and Simetrical.</p>

<h3>Instructions</h3>

<p>Just specify a language, talk page cat and main page template (all cAse SenSItive) and press 'Go!'. The PHP is
	designed to give you a usable URL that you can give to others.</p>

<form action="infoboxchecker.php" method="POST" onsubmit="return sub()">
	<p>Site: <input type="text" name="lang" style="width: 50px" value="<?php if( isset( $_GET['lang'] ) ) { echo $_GET['lang']; } ?>"/>.wikipedia.org</p>

	<p>Talk page category:<input type="text" name="category" style="width: 200px"
	                             value="<?php if( isset( $_GET['category'] ) ) { echo $_GET['category']; } ?>"/> <input type="checkbox" name="inverse"
	                                                                               style="margin-left:20px;margin-right:8px;" <?php if( isset( $_GET['inverse'] ) && $_GET['inverse'] ){
			echo 'checked="checked"';
		} ?>" value="true"/> Inverse?</p>

	<p>Article template: <input type="text" name="template" style="width: 200px"
	                            value="<?php if( isset( $_GET['template'] ) ) { echo $_GET['template']; } ?>"/></p>
	<input type="submit" value="Go!"/>
</form>
<?php
	if( isset( $_GET['lang'] )  ){
		echo "<h3>Probably incorrectly tagged ($count in total):</h3>\n<ul>\n";
		for( $i = 0; $i < count( $tagged ); $i++ ){
			$nt = $tagged[$i];
			echo "<li><a href=\"http://" . $lang . ".wikipedia.org/wiki/$nt\">$nt</a> (";
			if( $lang == "en" ){
				echo "<a href=\"http://" . $lang . ".wikipedia.org/wiki/Talk:$nt\">talk</a> | ";
			}
			echo "<a href=\"http://" . $lang . ".wikipedia.org/w/index.php?title=$nt&action=edit\">edit</a>)</li>\n";
		}
		echo "</ul>\n";
	}
	echo get_html( 'footer' );