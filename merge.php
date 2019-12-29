<?php

//
// Merge individual Givero bang files into one big, sorted JSON file
//
// Run from the folder with checked out bang files.
//

function sort_bangs_by_relevancy(&$bangs)
{
    usort($bangs, function($a, $b)
    {
        // No rnk parameter means 0 rank
        $ar = isset($a['rnk']) ? $a['rnk'] : 0;
        $br = isset($b['rnk']) ? $b['rnk'] : 0;


        if($ar == $br)
        {
        	// If no rank, use key as indicator
			if( strlen($a['key']) !== strlen($b['key']) )
			{
				// Compare length if they are different
            	return strlen($a['key']) < strlen($b['key']) ? -1 : 1;
            }
            
            // Compare the strings if length is the same
           	return $a['key'] < $b['key'] ? -1 : 1;
		}
        return $ar < $br ? 1 : -1;
    });
}


$keys=array();
$bangs=array();

$files = glob("./*.json");

foreach($files as $f)
{
	echo $f . "\r\n";
	
	if( $f === "./searchBangs.json" )
	{
		continue;
	}
	
	$json_content = file_get_contents($f);

	if($json_content === false)
	{
		echo "Could not read input file";
		die;
	}


	$arr = json_decode($json_content, true);
	
	foreach($arr as $elem)
	{
		if( !isset($elem['key']) )
		{
			echo "ERROR parsing array for 'key' in " . $f . "\r\n";
			var_dump($elem);
			die;
		}
		if( !isset($elem['name']) )
		{
			echo "ERROR parsing array for 'name' in " . $f . "\r\n";
			var_dump($elem);
			die;
		}
		if( !isset($elem['url']) )
		{
			echo "ERROR parsing array for 'url' in " . $f . "\r\n";
			var_dump($elem);
			die;
		}
		if( strpos($elem['url'], "%s") === false )
		{
			echo "ERROR, %s not present in 'url' in " . $f . "\r\n";
			var_dump($elem);
			die;
		}


		/*
			Element output format:
	        {
	                "key": "g",
	                "name": "Google",
	                "dom": "www.google.com",
	                "url": "https://www.google.com/search?q=@@q@@"
	        },
		*/
		
		$key = $elem['key'];
		$name = $elem['name'];
		$url = $elem['url'];
		
		if( isset($keys[$key]) )
		{
			echo "DUPLICATE KEY [" . $key . "] found in " . $f . "\r\n";
			die;
		}
		
		$a=array();
		$a['key'] = $key;
		$a['name'] = $name;
		$a['url'] = str_replace("%s", "@@q@@", $url);
		$a['dom'] = parse_url($url)['host'];

		array_push($bangs, $a);
	}	

}

sort_bangs_by_relevancy($bangs);

file_put_contents('searchBangs.json', json_encode($bangs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

?>
