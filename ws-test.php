<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Search - Open Concept Lab</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>

<h2>Open Concept Lab - Web Services API Examples</h2>

<div class="row-responsive">
<div class="accordion" id="accordion2">
<?php

/****************************************************************************
 * Initialize
 ***************************************************************************/

	$username      =  'admin';
	$password      =  'OpenCL_8';


/****************************************************************************
 ** CONCEPT: Setup
 ***************************************************************************/

	$dict          =  'CIEL';
	$concept_id    =  1643;
	$concept_uuid  =  '1643AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';


	/****************************************************************************
	 * CONCEPT: Solr
	 ***************************************************************************/

		$url = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*&q=' . 
				urlencode('+dict:' . $dict . ' +id:' . $concept_id);
		$json = debugCurl(
				'1', 
				'CONCEPT Solr', 
				$url);

	/****************************************************************************
	 * CONCEPT: OpenMRS REST API
	 ***************************************************************************/

		$url  =  'http://openconceptlab.org:8080/openmrs-' . 
				urlencode(strtolower($dict)) 
				. '/ws/rest/v1/concept/' . 
				urlencode($concept_uuid);
		$json = debugCurl(
				'2', 
				'CONCEPT OpenMRS REST API', 
				$url, 
				$username, 
				$password);


/****************************************************************************
 ** PIH SEARCH: Setup
 ***************************************************************************/

	$dict   =  'PIH'     ;
	$query  =  'malaria'  ;
	$start  =  0          ;
	$rows   =  5          ;


	/****************************************************************************
	 * PIH SEARCH: Solr
	 ***************************************************************************/

		$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
		$url .= '&start=' . $start . '&rows=' . $rows;
		$json = debugCurl(
				'3', 
				'PIH SEARCH Solr', 
				$url);


	/****************************************************************************
	 * PIH SEARCH: OpenMRS REST API
	 ***************************************************************************/

		$url  =  'http://openconceptlab.org:8080/openmrs-' . 
				urlencode(strtolower($dict)) .
				'/ws/rest/v1/concept/?q=' . 
				urlencode($query) .
				'&limit=' . $rows . 
				'&startIndex=' . $start;
		$json = debugCurl(
				'4', 
				'PIH SEARCH OpenMRS REST API', 
				$url, 
				$username, 
				$password);


/****************************************************************************
 ** CIEL SEARCH: Setup
 ***************************************************************************/

	$dict   =  'CIEL'     ;
	$query  =  'malaria'  ;
	$start  =  0          ;
	$rows   =  5          ;


	/****************************************************************************
	 * CIEL SEARCH: Solr
	 ***************************************************************************/

		$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
		$url .= '&start=' . $start . '&rows=' . $rows;
		$json = debugCurl(
				'5', 
				'CIEL SEARCH Solr', 
				$url);


	/****************************************************************************
	 * CIEL SEARCH: OpenMRS REST API
	 ***************************************************************************/

		$url  =  'http://openconceptlab.org:8080/openmrs-' . 
				urlencode(strtolower($dict)) .
				'/ws/rest/v1/concept/?q=' . 
				urlencode($query) .
				'&limit=' . $rows . 
				'&startIndex=' . $start;
		$json = debugCurl(
				'6', 
				'CIEL SEARCH OpenMRS REST API', 
				$url, 
				$username, 
				$password);


/****************************************************************************
 ** AMPATH SEARCH: Setup
 ***************************************************************************/

	$dict   =  'AMPATH'     ;
	$query  =  'malaria'  ;
	$start  =  0          ;
	$rows   =  5          ;


	/****************************************************************************
	 * AMPATH SEARCH: Solr
	 ***************************************************************************/

		$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
		$url .= '&start=' . $start . '&rows=' . $rows;
		$json = debugCurl(
				'7', 
				'AMPATH SEARCH Solr', 
				$url);


	/****************************************************************************
	 * AMPATH SEARCH: OpenMRS REST API
	 ***************************************************************************/

		$url  =  'http://openconceptlab.org:8080/openmrs-' . 
				urlencode(strtolower($dict)) .
				'/ws/rest/v1/concept/?q=' . 
				urlencode($query) .
				'&limit=' . $rows . 
				'&startIndex=' . $start;
		$json = debugCurl(
				'8', 
				'AMPATH SEARCH OpenMRS REST API', 
				$url, 
				$username, 
				$password);


/****************************************************************************
 ** COLLECTION: Setup
 ***************************************************************************/

	$collection_id  =  '4'  ;


	/****************************************************************************
	 * COLLECTION: Solr
	 ***************************************************************************/

		/*
		$url  = 'http://openconceptlab.org:8080/solr/db/select?wt=json&fl=*';
		$url .=  '&q=' . urlencode('+dict:' . $dict . ' +' . $query);
		$url .= '&start=' . $start . '&rows=' . $rows;
		$json = debugCurl(
				'9', 
				'SEARCH Solr', 
				$url);
		*/


	/****************************************************************************
	 * COLLECTION: OpenMRS REST API
	 ***************************************************************************/

		/*
		$url  =  'http://openconceptlab.org:8080/openmrs-' . 
				urlencode(strtolower($dict)) 
				. '/ws/rest/v1/concept/' . 
				urlencode($concept_uuid) .
				'?limit=' . $rows . 
				'&startIndex=' . $start;
		$json = debugCurl(
				'10', 
				'SEARCH OpenMRS REST API', 
				$url, 
				$username, 
				$password);
		*/
?>

</div> <!-- accordian -->
</div> <!-- container -->

    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

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

function debugCurl($curl_id, $curl_title, $url, $username='', $password='')
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
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse<?php echo $curl_id; ?>">
        <?php echo '<strong>' . $curl_title . ':</strong> ' . $url; ?>
      </a>
    </div>
    <div id="collapse<?php echo $curl_id; ?>" class="accordion-body collapse">
      <div class="accordion-inner">
		<?php echo '<strong>Output:</strong><pre>', ($json ? indent_json($json) : 'FAILURE'), '</pre>'; ?>
      </div>
    </div>
  </div>
<?php
}

?>