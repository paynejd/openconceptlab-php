<?php

define(  'MCL_VIEW_CONDENSED'  ,  1  );
define(  'MCL_VIEW_EXPANDED'   ,  2  );
define(  'MCL_VIEW_DEFAULT'    ,  MCL_VIEW_CONDENSED  );


/****************************************************************************
 *  Handle the search parameters
 ***************************************************************************/

	$arr_default_params = array(
			'q'      =>  ''     ,
			'debug'  =>  false  ,
			'start'  =>  0      ,
			'rows'   =>  20     ,
		);

	$arr_params = array_merge($arr_default_params, $_GET, $_POST);
	$q      =  $arr_params[  'q'      ];
	$debug  =  $arr_params[  'debug'  ];
	$start  =  $arr_params[  'start'  ];
	$rows   =  $arr_params[  'rows'   ];


/****************************************************************************
 *  Perform the search
 ***************************************************************************/

$r = null;
if ($q)
{

	// Set the search url
		$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url .= '&q=' . urlencode($q);
		$url .= '&start=' . $start . '&rows=' . $rows;

	// Perform the query
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($ch);
		curl_close($ch);
		if (!$json) {
			var_dump($json);
			trigger_error('curl failed');
			exit();
		}

	// Decode json
		$r = json_decode($json);

}


/****************************************************************************
 *	Setup the display
 ***************************************************************************/

	// Renderer
		$csrr = null;
		if ($r) {
			$csrr = new ConceptSearchResultsRenderer();
			$csrr->debug = $debug;
		}

	// Pagination settings
		$cur_page           =  ceil(  ($start + 1) / $rows  );
		$max_page           =  ceil(  $r->response->numFound / $rows  );
		$max_start_row      =  $start + $rows;
		$is_enabled_prev    =  ($cur_page == 1 ? false : true);
		$is_enabled_next    =  ($cur_page == $max_page ? false : true);
		$num_display_pages  =  10;
		$display_page_min   =  max(1, $cur_page - ceil(($num_display_pages - 1) / 2) );
		$display_page_max   =  min($max_page, $display_page_min + $num_display_pages - 1);


/**
 * Default object for rendering the search results. Extend this object 
 * to create other renderers.
 */
class ConceptSearchResultsRenderer
{
	public $mode            =  MCL_VIEW_DEFAULT;
	public $debug           =  false;
	public $display_header  =  false;

	function render($r)
	{
		$this->start($r);
		$this->displayHeader($r);
		$this->startBody($r);
		$this->displayResults($r);
		$this->endBody($r);
		$this->end($r);
	}

	function start($r) 
	{
		echo '<table id="results_table" ' . 
				'class="table table-striped table-condensed results-table">';
	}
	function end($r) 
	{
		echo '</table>';
	}
	function displayHeader($r) 
	{
		if ($this->display_header)
		{
			echo '<thead>';
			echo '<tr>';
			echo '<th></th>';	// checkbox
			echo '<th></th>';	// star
			echo '<th></th>';	// id
			echo '<th>Details</th>';	// details
			//echo '<th>Source</th>';		// source
			echo '</tr>';
			echo '</thead>';
		}
	}
	function startBody($r) 
	{
		echo '<tbody>';
	}
	function endBody($r) 
	{
		echo '</tbody>';
	}
	function displayResults($r) 
	{
		foreach ($r->response->docs as $c) {
			$this->startConcept($r, $c);
			$this->renderConcept($r, $c);
			$this->endConcept($r, $c);
		}
	}
	function startConcept($r, $c) 
	{
		echo '<tr>';
	}
	function renderConcept($r, $c) 
	{
		// Checkbox
		echo '<td class="col-checkbox"><input type="checkbox" /></td>';

		// Star
		echo '<td class="col-star"><img src="images/star4.png" alt="Star" /></td>';

		// Concept ID
		$tooltip_content = 
				'<table>' .
				'<tr><td>UUID:</td><td>' . 
					substr($c->uuid, 0, floor(strlen($c->uuid) / 2) ) . 
					'<br />' .
					substr($c->uuid, floor(strlen($c->uuid) / 2), strlen($c->uuid) ) . 
					'</td></tr>' .
				'<tr><td>Created:</td><td>' . $c->timestamp . '</td></tr>' .
				'</table>';

		echo '<td class="col-conceptid overflow-ellipsis">';
		echo '<a href="#" rel="popover" data-html="true" data-trigger="hover" data-placement="top" data-content="' . htmlentities($tooltip_content) . '" title="' . (isset($c->full_id) ? str_replace('_', '-', $c->full_id) : '[full_id]') . ' Details">';
		echo '<span class="concept-id">';
		echo (isset($c->full_id) ? str_replace('_', '-', $c->full_id) : '[full_id]');
		echo '</span>';
		echo '</a>';
		echo '</td>';

		// Concept details
		echo '<td class="col-name">';

			// Name and summary
			echo '<div class="overflow-ellipsis">';
			echo '<span class="preferred-concept-name">' . (isset($c->pname) ? $c->pname : '[pname]') . '</span> ';
			echo '<span class="concept-class">[ ' . (isset($c->class) ? $c->class : '<em>class</em>') . ' / ' . 
					(isset($c->datatype) ? $c->datatype : '<em>datatype</em>') . ' ]</span> ';
			echo '<span class="concept-description">' . (isset($c->description) ? ' - ' . $c->description : '') . '</span>';
			echo '</div>';

			// Mappings
			if (  $arr_elements = explode(' | ', $c->name)  ) {
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Mappings:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-mapping">' . $element . '</span>';
				}
				echo '</div>';
			}

			// Collections
			if (  $arr_elements = explode(' | ', $c->name)  ) {
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Collections:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-collection">' . $element . '</span>';
				}
				echo '</div>';
			}

			// Concept Sets
			if (  $arr_elements = explode(' | ', $c->name)  ) {
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Concept Sets:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-conceptset">' . $element . '</span>';
				}
				echo '</div>';
			}

		echo '</td>';
	}
	function endConcept($r, $c) 
	{
		echo '</tr>';

		if ($this->debug) {
			echo '<tr><td colspan="4" class="debug">';
			print_r($c);
			echo '</td></tr>';
		}
	}
}


?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Search - Open Concept Lab</title>

	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen" />
	<style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
		span.concept-id {
			font-weight: bold;
			color: #08c;
			font-size: 10pt;
		}
		span.preferred-concept-name {
			font-weight: bold;
			color: Black;
			font-size: 10pt;
		}
		span.concept-class {
			/*font-style: italic;*/
			/*color: #999;*/
			color: Black;
			font-size: 9pt;
		}
		span.concept-description {
			color: #999;
			font-size: 9pt;
		}

		td.col-checkbox { width: 15px; padding-right:0; vertical-align: top;}
		td.col-checkbox input { padding-top:0; margin-top:0;}
		td.col-star { width: 20px; padding-left:10px;}
		td.col-conceptid { width: 90px; }
		td.col-name { /*width: *;*/ }
		td.col-mapcode { width: 50px; }
		td.debug { white-space: pre; }

		.results-table {
			table-layout: fixed;
			width: 100%;
		}
		.overflow-ellipsis {
    		overflow: hidden;
    		text-overflow: ellipsis;
    		white-space: nowrap;
		}
		.popover-title {
			font-weight: bold;
		}
		.popover-content, .popover-content td {
			font-size: 9pt;
		}

		.subheading {
			font-size: 9pt;
			font-weight: 500;
			color: #666;
		}
		.bubble,
		.bubble-mapping,
		.bubble-collection,
		.bubble-conceptset
		{
			background-color: #ddd;
			border: 1px solid #ccc;
			padding-top: 1px;
			padding-bottom: 1px;
			padding-left: 6px;
			padding-right: 6px;
			border-radius: 5px;
			font-size: 9pt;
			margin-right: 3px;
			color: #333;
		}
		.bubble-mapping { background-color: #bee2fa; }
		.bubble-collection { background-color: #ffe0a7; }
		.bubble-conceptset { background-color: #ffffcc; }
	</style>
	<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="#">Open Concept Lab</a>
			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="active"><a href="wiki/">Search</a></li>
					<li><a href="wiki/">Wiki</a></li>
				</ul>
				<form class="navbar-form pull-right" action="signin.php" method="post">
					<fieldset>
						<input class="span2" type="text" id="uid" name="uid" placeholder="Email">
						<input class="span2" type="password" id="pwd" name="pwd" placeholder="Password">
						<button type="submit" class="inline btn btn-small">Sign In</button>
						<a class="btn btn-link btn-small" href="signup.php">Sign Up</a>
					</fieldset>
				</form>
			</div><!-- .nav-collapse -->
		</div><!-- .container -->
	</div><!-- .navbar-inner -->
</div><!-- .navbar -->

<?php if ($debug) { ?>
<div class="container" id="debug">
	<div class="row">
		<div class="span12">
		<?php
			echo '<strong>DEBUG - Solr Query:</strong><pre>' . htmlentities($url) . '</pre>';
		?>
		</div>
	</div>
</div><!-- .container #debug -->
<hr />
<?php } ?>

<div class="container-fluid">

	<div class="row-fluid">

		<!-- Browse Panel -->
		<div class="span2" id="panel-browse">
			<ul class="nav nav-list">
				<li class="nav-header">Dictionaries</li>
				<li><a href="search.php?q=dict%3A*">[All Dictionaries]</a></li>
				<li class="active"><a href="search.php?q=%2Bdict%3ACIEL">CIEL</a></li>
				<li><a href="search.php?q=%2Bdict%3APIH">PIH</a></li>
				<li><a href="search.php?q=%2Bdict%3AAMPATH">AMPATH</a></li>
				<li class="nav-header">Map Sources</li>
				<li><a href="#">SNOMED CT</a></li>
				<li><a href="#">ICD-10-WHO</a></li>
				<li><a href="#">RxNorm</a></li>
				<li><a href="#">LOINC</a></li>
				<li><a href="#">HL-7</a></li>
				<li><a href="#">AMPATH</a></li>
				<li><a href="#">PIH</a></li>
				<li><a href="#">More...</a></li>
				<li class="nav-header">Public Collections</li>
				<li><a href="#">Community Antenatal Care</a></li>
				<li><a href="#">More...</a></li>
			</ul>
		</div> <!-- .span2 #panel-browse -->


		<!-- Collection Panel -->
		<div class="span2 pull-right" id="panel-collection">
        	<div class="well well-small sidebar-nav">
				<ul class="nav nav-list">
					<li class="nav-header">Public Collections</li>
					<li><a href="#">Community ANC</a></li>
					<li><a href="#">Community PNC</a></li>
					<li><a href="#">Family Planning</a></li>
					<li><a href="#">MCL Core</a></li>
					<li class="nav-header"><a href="#"><img src="images/star-lit4.png" alt="Star" /> My Starred Concepts</a></li>
					<li class="nav-header">My Collections</li>
					<li><a href="#">MoTeCH Ghana ANC</a></li>
					<li><a href="#">CARE India ANC</a></li>
					<li><a href="#">Rwanda RapidSMS</a></li>
					<li><a href="#">Dimagi India</a></li>
				</ul>
			</div>
		</div>	<!-- .span2 #panel-collection -->


		<!-- Middle Panel -->
		<div class="span8" id="panel-middle">


			<!-- Search Bar -->
			<div class="row-fluid" id="panel-search-bar">
				<div class="span12" style="text-align: center;">
					<form action="search.php" method="get" class="form-search">
						<fieldset>
							<div class="input-append">
								<input type="text" class="input-xxlarge search-query" placeholder="Search" name="q" value="<?php echo $q; ?>" />
								<button type="submit" class="btn">Search</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div><!-- .row-fluid #panel-search-bar -->


			<!-- Search Results Summary -->
			<div class="row-fluid" id="panel-search-summary">
				<?php   
				if ($csrr && $r)
				{
					echo '<div class="well well-small" style="clear:both;">';
					//echo '<div class="span12">';
					if ($r->response->numFound) 
					{
						$range_a  =  $start + 1;
						$range_b  =  min(  $start + $rows  ,  $r->response->numFound  );

						echo 'Showing results <strong>' . $range_a . '</strong> ' .
								'to <strong>' . $range_b . '</strong> of ' . $r->response->numFound . '.'; 
					} else {
						echo 'Your search did not match any concepts.';
					}
					echo '</div>';
				}
				?>
			</div><!-- .row-fluid #panel_searchbar -->


			<!-- Search Results -->
			<div class="row-fluid" id="panel-search-results">
				<div class="span12">
				<?php
					if ($csrr && $r)  {
						$csrr->render($r);
					}  else  {
						echo '<p><em>No search results</em></p>';
					}
				?>
				</div>
			</div><!-- .row-fluid #panel-search-results -->


			<!-- Pagination -->
			<div class="row-fluid" id="panel-pagination">
				<div class="span12">
					<div class="pagination pagination-centered">
						<ul>
							<?php
								if ($is_enabled_prev) {
									echo '<li><a href="' . buildSearchUrl(array_merge($arr_params, array('start'=>$start-$rows)), $arr_default_params) . '">';
									echo '&laquo; Prev</a></li>';
								} else {
									echo '<li class="disabled"><a href="#">&laquo; Prev</a></li>';
								}
								if ($display_page_min > 1) {
									echo '<li><a href="' . buildSearchUrl(array_merge($arr_params, array('start'=>0)), $arr_default_params) . '">1';
									if ($display_page_min > 2) echo '...';
									echo '</a></li>';
								}
								for ($i = $display_page_min; $i <= $display_page_max; $i++) 
								{
									$cur_start = ($i - 1) * $rows;
									echo '<li ' . ($i == $cur_page ? 'class="active"' : '');
									echo '><a href="' . buildSearchUrl(array_merge($arr_params, array('start'=>$cur_start)), $arr_default_params) . '">';
									echo $i . '</a></li>';
								}
								if ($max_page > $display_page_max) {
									$url = buildSearchUrl(array_merge($arr_params, array('start'=>($max_page-1)*$rows)), $arr_default_params);
									$text = $max_page;
									if ($max_page > ($display_page_max + 1)) 	$text = '...' . $text;
									echo '<li><a href="' . $url . '">' . $text . '</a></li>';
								}
								if ($is_enabled_next) {
									echo '<li><a href="' . buildSearchUrl(array_merge($arr_params, array('start'=>$max_start_row)), $arr_default_params) . '">';
									echo 'Next &raquo;</a></li>';
								} else {
									echo '<li class="disabled"><a href="#">Next &raquo;</a></li>';
								}
							?>
						</ul>
					</div>	<!-- pagination -->
				</div>	<!-- span12 -->
			</div>	<!-- .row-fluid #panel-pagination -->

		</div><!-- .span8 #panel-middle -->

	</div><!-- .row-fluid -->
</div><!-- .container-fluid -->


    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="bootstrap/js/application.js"></script>
    <script>
	    /*$('#results_table').tooltip({
			selector: "a[rel=tooltip]"
		});*/
    </script>


</body>
</html>
<?php

function buildSearchUrl($arr_params, $arr_default_params=null) {
	if (!$arr_default_params) $arr_default_params = array();
	$url = 'search.php?';
	$i = 0;
	foreach ($arr_params as $k => $v) {
		if (isset($arr_default_params[$k]) && $arr_default_params[$k] == $v) continue;
		if ($i) $url .= '&amp;';
		$url .= urlencode($k) . '=' . urlencode($v);
		$i++;
	}
	return $url;
}

?>