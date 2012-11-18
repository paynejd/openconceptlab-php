<?php

define('MCL_VIEW_CONDENSED', 1);
define('MCL_VIEW_EXPANDED', 2);
define('MCL_VIEW_DEFAULT', MCL_VIEW_CONDENSED);


/****************************************************************************
 *  Handle the search parameters
 ***************************************************************************/

	$q      =  (  isset($_GET[  'q'      ]) ? $_GET[  'q'      ] : ''  );
	$debug  =  (  isset($_GET[  'debug'  ]) ? $_GET[  'debug'  ] : ''  );
	$start  =  (  isset($_GET[  'start'  ]) ? $_GET[  'start'  ] : 0   );
	$rows   =  (  isset($_GET[  'rows'   ]) ? $_GET[  'rows'   ] : 20  );


/****************************************************************************
 *  Perform the search
 ***************************************************************************/

$r = null;
if ($q)
{

	// Set the search url
		$url_base = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url = $url_base . '&q=' . urlencode($q);
		$url .= '&start=' . $start;
		if ($rows) $url .= '&rows=' . $rows;

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

	// Build urls for next/prev results
		$url_next = '';
		if (  $r->response->numFound > ($start + $rows)  ) {
			$next_start = $start + $rows;
			$url_next = 'search.php?q=' . urlencode($q) .
					'&start=' . $next_start .
					'&rows=' . $rows;
			if ($debug) $url_next .= '&debug=1';
		}

		$url_prev = '';
		if ($start > 0) {
			$prev_start = $start - $rows;
			if ($prev_start < 0) $prev_start = 0;
			$url_prev = 'search.php?q=' . urlencode($q) . 
					'&start=' . $prev_start . 
					'&rows=' . $rows;
			if ($debug) $url_prev .= '&debug=1';
		}

}


/****************************************************************************
 *	Setup the renderer
 ***************************************************************************/

$csrr = null;
if ($r) {
	$csrr = new ConceptSearchResultsRenderer();
	$csrr->debug = $debug;
}



class ConceptSearchResultsRenderer
{
	public $mode = MCL_VIEW_DEFAULT;
	public $debug = false;
	public $display_header = false;

	function render($r)
	{
		$this->start($r);
		$this->displayHeader($r);
		$this->startBody($r);
		$this->displayResults($r);
		$this->endBody($r);
		$this->end($r);
	}

	function start($r) {
		echo '<table class="table table-striped table-condensed table-hover results-table">';
	}
	function end($r) {
		echo '</tr>';
	}
	function displayHeader($r) {
		if ($this->display_header)
		{
			echo '<thead>';
			echo '<tr>';
			echo '<th></th>';	// checkbox
			echo '<th></th>';	// star
			echo '<th></th>';	// id
			echo '<th>Details</th>';	// details
			echo '<th>Source</th>';		// source
			echo '</tr>';
			echo '</thead>';
		}
	}
	function startBody($r) {
		echo '<tbody>';
	}
	function endBody($r) {
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
	function startConcept($r, $c) {
		echo '<tr>';
	}
	function renderConcept($r, $c) 
	{
		// Checkbox
		echo '<td class="col-checkbox"><input type="checkbox" /></td>';

		// Star
		echo '<td class="col-star"><img src="images/star4.png" /></td>';

		// Concept ID
		echo '<td class="col-conceptid overflow-ellipsis">';
		echo '<span class="concept-id">' . 
				(isset($c->full_id) ? str_replace('_', '-', $c->full_id) : '[full_id]') . 
				'</span>';
		echo '</td>';

		// Name and summary
		echo '<td class="col-name overflow-ellipsis">';
		//echo '<span class="concept-id">' . $c->id . '</span> - ';
		echo '<span class="preferred-concept-name">' . (isset($c->pname) ? $c->pname : '[pname]') . '</span> ';
		echo '<span class="concept-class">[ ' . (isset($c->class) ? $c->class : '[class]') . ' / ' . 
				(isset($c->datatype) ? $c->datatype : '[datatype]') . ' ]</span> ';
		echo '<span class="concept-description"> - ' . (isset($c->description) ? $c->description : '') . '</span>';
		echo '</td>';

		// Map codes
		//echo '<td class="col-mapcode">' . $c->source . '</td>';
	}
	function endConcept($r, $c) {
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
		}
		span.concept-class {
			/*font-style: italic;*/
			/*color: #999;*/
			color: Black;
		}
		span.concept-description {
			color: #999;
		}

		td.col-checkbox { width: 15px; padding-right:0; vertical-align: middle;}
		td.col-checkbox input { padding-top:0; margin-top:0;}
		td.col-star { width: 20px; padding-left:10px;}
		td.col-conceptid { width: 90px; }
		td.col-name { width: *; }
		td.col-mapcode { width: 50px; }
		td.debug { white-space: pre; }

		.results-table {
			table-layout: fixed;
			width: 100%;
		}
		.results-table td {
			/*border: 1px solid black;*/
			font-size: 10pt;
		}
		.overflow-ellipsis {
    		overflow: hidden;
    		text-overflow: ellipsis;
    		white-space: nowrap;
    		color: #999;
		}
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
			</div><!--/.nav-collapse -->
		</div>
	</div>
</div>

<?php if ($debug) { ?>
<div class="container" id="debug">
	<div class="row">
		<div class="span12">
		<?php
			echo '<strong>DEBUG - Solr Query:</strong><pre>' . htmlentities($url) . '</pre>';
		?>
		</div>
	</div>
</div>
<hr />
<?php } ?>

<div class="container-fluid">

	<div class="row-fluid">

		<!-- Browse Panel -->
		<div class="span2">
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
		</div>


		<!-- Collection Panel -->
		<div class="span2 pull-right">
        	<div class="well well-small sidebar-nav">
				<ul class="nav nav-list">
					<li class="nav-header">Public Collections</li>
					<li><a href="#">Community ANC</a></li>
					<li><a href="#">Community PNC</a></li>
					<li><a href="#">Family Planning</a></li>
					<li><a href="#">MCL Core</a></li>
					<li class="nav-header"><a href="#"><img src="images/star-lit4.png" /> My Starred Concepts</a></li>
					<li class="nav-header">My Collections</li>
					<li><a href="#">MoTeCH Ghana ANC</a></li>
					<li><a href="#">CARE India ANC</a></li>
					<li><a href="#">Rwanda RapidSMS</a></li>
					<li><a href="#">Dimagi India</a></li>
				</ul>
			</div>
		</div>


		<div class="span8">

			<!-- Search Bar -->
			<div class="span12" style="text-align: center;">
				<form action="search.php" method="get" class="form-search">
					<fieldset>
						<div class="input-append">
							<input type="text" class="input-xlarge search-query" placeholder="Search" name="q" value="<?php echo $q; ?>" />
							<button type="submit" class="btn">Search</button>
						</div>
					</fieldset>
				</form>
			</div>

			<!-- Search Results Summary -->
<?php   
			if ($csrr && $r && $r->response->numFound)  
			{
				$range_a  =  $start + 1;
				$range_b  =  min(  $start + $rows  ,  $r->response->numFound  );
?>
			<div class="well well-small" style="clear:both;">
				Showing results <strong><?php echo $range_a; ?></strong> 
				to <strong><?php echo $range_b; ?></strong> of
				<?php echo $r->response->numFound; ?>. 
<?php
				if ($url_prev) {
					echo '<a href="' . $url_prev . '" class="btn">&lt; Previous</a>';
				}
				if ($url_next) {
					echo '<a href="' . $url_next . '" class="btn">Next &gt;</a>';
				}
			} else if ($csrr && $r && !$r->response->numFound) {
				echo '<div class="well well-small" style="clear:both;">Your search did not match any concepts.</div>';
			}
?>
			</div>

			<!-- Search Results -->
<?php
			if ($csrr && $r) {
				$csrr->render($r);
			} else {
				echo '<p><em>No search results</em></p>';
			}
?>
		</div>

	</div>
</div>


    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap-transition.js"></script>
    <script src="bootstrap/js/bootstrap-alert.js"></script>
    <script src="bootstrap/js/bootstrap-modal.js"></script>
    <script src="bootstrap/js/bootstrap-dropdown.js"></script>
    <script src="bootstrap/js/bootstrap-scrollspy.js"></script>
    <script src="bootstrap/js/bootstrap-tab.js"></script>
    <script src="bootstrap/js/bootstrap-tooltip.js"></script>
    <script src="bootstrap/js/bootstrap-popover.js"></script>
    <script src="bootstrap/js/bootstrap-button.js"></script>
    <script src="bootstrap/js/bootstrap-collapse.js"></script>
    <script src="bootstrap/js/bootstrap-carousel.js"></script>
    <script src="bootstrap/js/bootstrap-typeahead.js"></script>


</body>
</html>