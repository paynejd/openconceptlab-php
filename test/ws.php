<?php

/****************************************************************************
 * Initialize
 ***************************************************************************/

	// omrs user
	$username      =  'admin'     ;
	$password      =  'OpenCL_8'  ;

	// individual concept
	$concept_id    =  1643        ;
	$concept_uuid  =  '1643AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

	// concept search
	$query  =  'malaria'  ;				
	$start  =  0          ;
	$rows   =  5          ;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Search - Open Concept Lab</title>
	<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen" />
	<style>
		pre.api-example {
			font-size: 8pt;
			line-height: 11pt;
		}
		body {
			padding: 15px 40px;
		}
	</style>
</head>
<body>

<h2>Open Concept Lab - Web Services API Examples</h2>

<h3>Solr API</h3>

<div class="row-responsive">
<div class="accordion" id="accordion2">
<?php

/****************************************************************************
 * SPECIFIC CONCEPT: Solr
 ***************************************************************************/

	$dict          =  'CIEL'      ;
	$url = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*&q=' . 
			urlencode('+dict:' . $dict . ' +id:' . $concept_id);
	$json = debugCurl(
			'accordion2',
			'10', 
			'INDIVIDUAL CONCEPT', 
			$url);


/****************************************************************************
 * CIEL SEARCH: Solr
 ***************************************************************************/

	$dict   =  'CIEL'     ;
	$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
	$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
	$url .= '&start=' . $start . '&rows=' . $rows;
	$json = debugCurl(
			'accordion2',
			'11', 
			'CIEL SEARCH', 
			$url);


/****************************************************************************
 * PIH SEARCH: Solr
 ***************************************************************************/

	$dict   =  'PIH'      ;
	$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
	$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
	$url .= '&start=' . $start . '&rows=' . $rows;
	$json = debugCurl(
			'accordion2',
			'12', 
			'PIH SEARCH', 
			$url);


/****************************************************************************
 * AMPATH SEARCH: Solr
 ***************************************************************************/

	$dict   =  'AMPATH'   ;
	$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
	$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
	$url .= '&start=' . $start . '&rows=' . $rows;
	$json = debugCurl(
			'accordion2',
			'13', 
			'AMPATH SEARCH', 
			$url);


?>
</div> <!-- accordion -->
</div> <!-- container -->


<h3>OpenMRS REST API</h3>
<div class="row-responsive">
<div class="accordion" id="accordion3">
<?php

/****************************************************************************
 * CONCEPT: OpenMRS REST API
 ***************************************************************************/

	$dict          =  'CIEL'      ;
	$url  =  'http://openconceptlab.org:8080/openmrs-' . 
			urlencode(strtolower($dict)) 
			. '/ws/rest/v1/concept/' . 
			urlencode($concept_uuid);
	$json = debugCurl(
			'accordion3',
			'20', 
			'INDIVIDUAL CONCEPT', 
			$url, 
			$username, 
			$password);


/****************************************************************************
 * CIEL SEARCH: OpenMRS REST API
 ***************************************************************************/

	$dict   =  'CIEL'     ;
	$url  =  'http://openconceptlab.org:8080/openmrs-' . 
			urlencode(strtolower($dict)) .
			'/ws/rest/v1/concept/?q=' . 
			urlencode($query) .
			'&limit=' . $rows . 
			'&startIndex=' . $start;
	$json = debugCurl(
			'accordion3',
			'21', 
			'CIEL SEARCH', 
			$url, 
			$username, 
			$password);


/****************************************************************************
 * PIH SEARCH: OpenMRS REST API
 ***************************************************************************/

	$dict   =  'PIH'      ;
	$url  =  'http://openconceptlab.org:8080/openmrs-' . 
			urlencode(strtolower($dict)) .
			'/ws/rest/v1/concept/?q=' . 
			urlencode($query) .
			'&limit=' . $rows . 
			'&startIndex=' . $start;
	$json = debugCurl(
			'accordion3',
			'22', 
			'PIH SEARCH', 
			$url, 
			$username, 
			$password);


/****************************************************************************
 * AMPATH SEARCH: OpenMRS REST API
 ***************************************************************************/

	$dict   =  'AMPATH'   ;
	$url  =  'http://openconceptlab.org:8080/openmrs-' . 
			urlencode(strtolower($dict)) .
			'/ws/rest/v1/concept/?q=' . 
			urlencode($query) .
			'&limit=' . $rows . 
			'&startIndex=' . $start;
	$json = debugCurl(
			'accordion3',
			'23', 
			'AMPATH SEARCH', 
			$url, 
			$username, 
			$password);

?>
</div> <!-- accordion -->
</div> <!-- container -->


<h3>Collections REST API</h3>
<div class="row-responsive">
<div class="accordion" id="accordion5">
<?php


/****************************************************************************
 * CONCEPT COLLECTIONS: OCL CustomAPI
 ***************************************************************************/

	$url  =  'http://openconceptlab.org/d/openconceptlab/ws/v1/collections.php?concept=CIEL:1371,CIEL:12';
	$json = debugCurl(
			'accordion5',
			'30', 
			'CONCEPT COLLECTION MEMBERSHIP', 
			$url, 
			$username, 
			$password);

?>

</div> <!-- accordion -->
</div> <!-- container -->

    <script src="../bootstrap/js/jquery.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
<?php




/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function indent_json($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}

function debugCurl($data_parent_id, $curl_id, $curl_title, $url, $username='', $password='')
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($username) {
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password); 
	}
	$json = curl_exec($ch);
	if (!$json) $err = curl_error($ch);
	curl_close($ch);
	$r = ($json ? json_decode($json) : false);

?>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#<?php echo htmlentities($data_parent_id); ?>" href="#collapse<?php echo $curl_id; ?>">
        <?php echo '<strong>' . $curl_title . ':</strong> ' . $url; ?>
      </a>
    </div>
    <div id="collapse<?php echo $curl_id; ?>" class="accordion-body collapse">
      <div class="accordion-inner">
		<?php echo '<strong>Output:</strong><pre class="api-example">', ($json ? indent_json($json) : 'FAILURE'), '</pre>'; ?>
      </div>
    </div>
  </div>
<?php
}

?>