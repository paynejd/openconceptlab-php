<?php

require_once('LocalSettings.inc.php');
require_once(OCL_ROOT . 'fw/ConceptSearchResultsRenderer.inc.php');


/****************************************************************************
 *  Handle the search parameters
 ***************************************************************************/

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
		$url_search  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url_search .= '&q=' . urlencode($q);
		$url_search .= '&start=' . $start . '&rows=' . $rows;

	// Perform the query
		$ch   =  curl_init($url_search);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json_search =  curl_exec($ch);	// Website returns json
		curl_close($ch);
		if (!$json_search) {
			var_dump($json_search);
			trigger_error('curl 1 failed');
			exit();
		}
		$r    =  json_decode($json_search);

		//echo '<pre>', var_dump($r), '</pre>';
		//exit();


	// Load collection info
		if (  isset($r->response->numFound)  &&  $r->response->numFound  )
		{
			// Grab the concept IDs
			$criteria = '';
			foreach ($r->response->docs as $c)
			{
				if ($criteria) $criteria .= ',';
				$criteria .= $c->dict . ':' . $c->id;
			}

			// Perform the query
			//$url_collection  =  'http://openconceptlab.org/ws/v1/collections.php?concept=' . $criteria;
			$url_collection  =  'http://localhost/~paynejd/openconceptlab/ws/v1/collections.php?concept=' . $criteria;
			$ch   =  curl_init($url_collection);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$json_collection =  curl_exec($ch);
			curl_close($ch);
			if (!$json_collection) {
				var_dump($json_collection);
				trigger_error('curl 2 failed');
				exit();
			}
			$collections = json_decode($json_collection);

			// Add collections to results object
			if (isset($collections->response->numFound) && $collections->response->numFound) 
			{
				foreach ($collections->response->docs as $coll)
				{
					foreach ($r->response->docs as $c)
					{
						if ($c->id == $coll->concept_id) {
							if (!isset($c->collections)) $c->collections = array();
							$c->collections[] = $coll;
						}
					}
				}
			}
		}

		//echo '<pre>', var_dump($r), '</pre>';

}


/****************************************************************************
 *	Setup the display
 ***************************************************************************/

		$csrr = null;
		if (  $r  &&  isset($r->error)  ) 
		{
			echo '<p>Error occurred:</p>';
			var_dump($r);
		} 
		elseif (  $r  &&  isset($r->response)  )
		{
			// Renderer
			$csrr = new ConceptSearchResultsRenderer();
			$csrr->debug = $debug;

			// Pagination settings
			$cur_page           =  ceil(  ($start + 1) / $rows  );
			$max_page           =  ceil(  $r->response->numFound / $rows  );
			$max_start_row      =  $start + $rows;
			$is_enabled_prev    =  ($cur_page == 1 ? false : true);
			$is_enabled_next    =  ($cur_page == $max_page ? false : true);
			$num_display_pages  =  10;
			$display_page_min   =  max(1, $cur_page - ceil(($num_display_pages - 1) / 2) );
			$display_page_max   =  min($max_page, $display_page_min + $num_display_pages - 1);
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
    		font-size: 9pt;
    		padding-top: 0px;
    		padding-bottom: 4px;
    		color: #999;
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
		.bubble-conceptset,
		.bubble-question,
		.bubble-answer
		{
			background-color: #eee;
			border: 1px solid #ccc;
			padding-top: 2px;
			padding-bottom: 2px;
			padding-left: 6px;
			padding-right: 6px;
			border-radius: 5px;
			font-size: 9pt;
			margin-right: 3px;
			color: #333;
		}
		/*
		.bubble:hover,
		.bubble-mapping:hover,
		.bubble-collection:hover,
		.bubble-conceptset:hover,
		*/
		.bubble-question:hover,
		.bubble-answer:hover
		{
			background-color: #bee2fa;
			border: 1px solid #99c;
			cursor: pointer;
			text-decoration: underline;
		}
		/*
		.bubble-mapping    { background-color: #bee2fa; }
		.bubble-collection { background-color: #ffe0a7; }
		.bubble-conceptset { background-color: #ffffcc; }
		.bubble-question   { background-color: #fbd;    }
		.bubble-answer     { background-color: #dfa;    }
		*/
		span.retired {
			color: #933;
			text-decoration: line-through;
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
			</div><!-- .nav-collapse -->
		</div><!-- .container -->
	</div><!-- .navbar-inner -->
</div><!-- .navbar -->

<?php if ($debug) { ?>
<div class="container" id="debug">
	<div class="row">
		<div class="span12">
		<?php
			echo '<strong>DEBUG - Solr Query:</strong><pre>' . htmlentities($url_search) . "\n" . htmlentities($url_collection) . '</pre>';
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
				<li><a href="search.php?q=%2BSNOMED%5C+CT%3A%5B*+TO+*%5D">SNOMED CT</a></li>
				<li><a href="search.php?q=%2BSNOMED%5C+NP%3A%5B*+TO+*%5D">SNOMED NP</a></li>
				<li><a href="search.php?q=%2BICD-10-WHO%3A%5B*+TO+*%5D">ICD-10-WHO</a></li>
				<li><a href="search.php?q=%2BICD-10-WHO%5C+2nd%3A%5B*+TO+*%5D">ICD-10-WHO 2nd</a></li>
				<li><a href="search.php?q=%2BLOINC%3A%5B*+TO+*%5D">LOINC</a></li>
				<li><a href="search.php?q=%2BRxNORM%3A%5B*+TO+*%5D">RxNORM</a></li>
				<li><a href="search.php?q=%2BHL-7%5C+CVX%3A%5B*+TO+*%5D">HL-7 CVX</a></li>
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
			<?php if (isset($r->response) && $csrr) { ?>
			<div class="row-fluid" id="panel-pagination">
				<div class="span12">
					<div class="pagination pagination-centered">
						<ul>
							<?php
								if ($is_enabled_prev) {
									echo '<li><a href="' . ConceptSearchResultsRenderer::buildSearchUrl(array_merge($arr_params, array('start'=>$start-$rows)), $arr_default_params) . '">';
									echo '&laquo; Prev</a></li>';
								} else {
									echo '<li class="disabled"><a href="#">&laquo; Prev</a></li>';
								}
								if ($display_page_min > 1) {
									echo '<li><a href="' . ConceptSearchResultsRenderer::buildSearchUrl(array_merge($arr_params, array('start'=>0)), $arr_default_params) . '">1';
									if ($display_page_min > 2) echo '...';
									echo '</a></li>';
								}
								for ($i = $display_page_min; $i <= $display_page_max; $i++) 
								{
									$cur_start = ($i - 1) * $rows;
									echo '<li ' . ($i == $cur_page ? 'class="active"' : '');
									echo '><a href="' . ConceptSearchResultsRenderer::buildSearchUrl(array_merge($arr_params, array('start'=>$cur_start)), $arr_default_params) . '">';
									echo $i . '</a></li>';
								}
								if ($max_page > $display_page_max) {
									$url = ConceptSearchResultsRenderer::buildSearchUrl(array_merge($arr_params, array('start'=>($max_page-1)*$rows)), $arr_default_params);
									$text = $max_page;
									if ($max_page > ($display_page_max + 1)) 	$text = '...' . $text;
									echo '<li><a href="' . $url . '">' . $text . '</a></li>';
								}
								if ($is_enabled_next) {
									echo '<li><a href="' . ConceptSearchResultsRenderer::buildSearchUrl(array_merge($arr_params, array('start'=>$max_start_row)), $arr_default_params) . '">';
									echo 'Next &raquo;</a></li>';
								} else {
									echo '<li class="disabled"><a href="#">Next &raquo;</a></li>';
								}
							?>
						</ul>
					</div>	<!-- pagination -->
				</div>	<!-- span12 -->
			</div>	<!-- .row-fluid #panel-pagination -->
			<?php } ?>

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