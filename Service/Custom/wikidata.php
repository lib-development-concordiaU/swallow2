<?php
include "../query.php";
include_once "../../Model/clase.php";
include_once "../../Model/db.config.php";

function getValue($in_array,$in_property){
    $result = '';
    foreach($in_array as $fact){
        if($fact["p"] == $in_property){
            $result = $fact["o"];
            break;
        }
    }   
    return $result;
}

//function checkForDuplicates($in_array,$in_property,$in_value)

function checkForDuplicates($in_claims,$in_property,$in_value){
    $result = false;
    foreach($in_claims as $claim){
        if( isset ($claim[1]) && isset ($claim[2]) ){
            if($claim[1] == $in_property && $claim[2] == $in_value){
                $result = true;
                break;
            }
        }
    }   
    return $result;
}

function wikidataViafLookup($in_viaf){
    $result = false;
    $url = 'https://www.wikidata.org/w/api.php?action=query&format=json&list=search&srsearch=haswbstatement:P214='.$in_viaf;
    $json = file_get_contents($url);
    $data = json_decode($json,true);
    if(isset($data['query']['search'][0]['title'])){
        $result = "https://www.wikidata.org/wiki/".$data['query']['search'][0]['title'];
    }
    return $result;
}

//write a function that calculates the similarity of two strings
function similarity($s1, $s2) {
    $s1 = strtolower($s1);
    $s2 = strtolower($s2);
    similar_text($s1, $s2, $percent);
    return $percent;
}

function apiCall($endPoint, $params){
    
    $url = $endPoint . "?" . http_build_query( $params );

    //var_dump($url);
    $ch = curl_init( $url );
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36'
          ),
        ));
    $output = curl_exec( $ch );
    curl_close( $ch );
    
    if(curl_errno($ch)){
        echo 'Curl error: ' . curl_error($ch);
    }
    return $output;
}


//Function that performs a sparql query that gets the item for a given itemLabel to wikidata and returns the results in json format
function wikidataLookup( $in_title){
    $returnValue = false;
    
    $sparqlQuery = "SELECT distinct ?item ?itemLabel WHERE{ ?item ?label \"".$in_title."\" @en. 
        SERVICE wikibase:label { bd:serviceParam wikibase:language \"en\"}    
        }";

    $endPoint = "https://query.wikidata.org/sparql";
    $params = [
        "query" => $sparqlQuery,
    ];
    
    $output = apiCall($endPoint,$params);

    $resultsArray = json_decode( $output, true );
    foreach( $resultsArray['results']['bindings'] as $result ){
        if(strpos($result['item']['value'],'wikidata.org/entity/Q') !== false){
            $returnValue = $result['item']['value'];
        }
    }

    return $returnValue;
    
}

function wikidataLocationLookup($in_latitude,$in_longitude,$in_label=''){
    $returnValue = false;

    $endPoint = "https://en.wikipedia.org/w/api.php";
    $params = [
        "action" => "query",
        "format" => "json",
        "list" => "geosearch",
        "gscoord" => $in_latitude."|".$in_longitude,
        "gsradius" => "10000",
        "gslimit" => "10"
    ];

    $output = apiCall($endPoint,$params);
    $resultsArray = json_decode( $output, true );
    $resultLabel = [];

    $bestResult=[];
    foreach( $resultsArray['query']['geosearch'] as $result ){
        if($in_label != ''){
            $similarity = similarity($in_label,$result['title']);
            if($similarity >= 50){
                if($bestResult == []){
                    $bestResult = ["similarity" => $similarity, "title" => $result['title']];
                }elseif($similarity > $bestResult['similarity']){
                        $bestResult = ["similarity" => $similarity, "title" => $result['title']];
                    }
            }
        }
    } //end of foreach

    if($bestResult != []){
        
        $returnValue = wikidataLookup($bestResult['title']);
    }
    
    return $returnValue;

}

//funtion that convert time in HH:MM:SS format to seconds
function timeToSeconds($in_time){
    $timeArray = explode(":",$in_time);
    $seconds = 0;
    $seconds += $timeArray[0] * 3600;
    $seconds += $timeArray[1] * 60;
    $seconds += $timeArray[2];
    return $seconds;
}

//function that extact the Qcode from a wikidata url
function getQcode($in_url){
    $qcode = '';
    $qcode = substr($in_url, strrpos($in_url, '/') + 1);
    return $qcode;
}

function generateQuickStatements($swallowLD){
    #serialize $swallowLD to associative array
    $swallowArray = json_decode($swallowLD,true);
    #get the row of $swallowArray that the secnd element is equal to 'http://purl.org/dc/terms/title'
    $title =getValue($swallowArray,'http://purl.org/dc/terms/title');

    $claims = [];
    $claims[] = ['CREATE'];
    $claims[] = ['LAST','Len','"'.$title.'"'];
    $claims[] = ['LAST','P361','Q117038029'];

    $coordinates = []; 

    $venueName = '';

    foreach($swallowArray as $fact){
        $predicate = $fact["p"];
        $object = $fact["o"];
        $claim = '';

        #if the predicate conatins 'wikidata.org ' then the claim is 'CREATE'
        if(strpos($predicate,'wikidata.org') !== false){
            #claim is a string with the format 'LAST TAB predicate TAB object'
            if($object != "" && $object != "-1"){ 
                $property = explode('Property:',$predicate);
                $entity = explode('/wiki/Q',$object);
            
                if(isset($entity[1])){ //is a Q code
                    #check for special cases
                    if($property[1] == 'P361' && $object == 'Q326342'){ //change the default property P361 (part of) to P485(archives at for the partner institution)
                        $property[1] = 'P485';
                    }
                    $claim = ['LAST',$property[1],'Q'.$entity[1]] ;
            
                }else{ // is not a Q code. check if a url
                    
                    if( count(explode('://',$object)) == 1){ //is not a url

                        #check for special cases
                        if($property[1] == 'P10135'){ //Property is P10135 then the object is a date (recording date)
                            $tokens = explode(' ',trim($object));
                            if(isset($tokens[1]) && $tokens[1] < 10){
                                $tokens[1] = '0'.$tokens[1];
                            }
                            if(isset($tokens[2]) && $tokens[2] < 10){
                                $tokens[2] = '0'.$tokens[2];
                            }
                            #trim emp[rty spaces at the begining and end of $object and then subtitude spaces with '-'
                            $object = '+'.implode('-',$tokens).'T00:00:00Z/11';
                        }
                        // check if is a venue
                        if($property[1] == 'P1071'){ //is the venue name. We'll process it later when we have the coordinates
                            $venueName = $object;
                            $object = null;
                        }
                        //check if the duration
                        if($property[1] == 'P2047'){ //is the duration
                            $object = timeToSeconds($object)."U11574";
                        }
                        
                    }else{//is a url
                        #check if the object is a VIAF code   
                        if(count(explode('viaf.org',$object)) == 2){
                            $viaf = explode('/',$object);
                            $viaf = $viaf[count($viaf)-1];
                            
                            $wikidata = wikidataViafLookup($viaf);
                            if($wikidata !== false){
                                $object = getQcode($wikidata);
                            }else{
                                $object = null;
                            }
                        }
                    }   
                    if(!is_null($object)){
                        $claim = ['LAST',$property[1],$object] ;                     
                    } 
                    
                }
                if($claim != ''){
                    if(!checkForDuplicates($claims,$claim[1],$claim[2])){
                        $claims[] = $claim;
                    }                
                }          
            }
        }
        #look for the latidude and longitude
        if($predicate == 'https://schema.org/latitude'){
            $coordinates[0] = $object;
        }elseif($predicate == 'https://schema.org/longitude'){
            $coordinates[1] = $object;
        }
    }
    
    if(count($coordinates) == 2){
        $claims[] = ['LAST','P625','@'.$coordinates[0].'/'.$coordinates[1]];
        //if theres a venue name, try to find the wikidata code for that
        if($venueName != ''){
            $venueQcode = wikidataLocationLookup($coordinates[0],$coordinates[1],$venueName);
            if($venueQcode != false){
                $claims[] = ['LAST','P1071',getQcode($venueQcode)];
            }
        }
       
    }

    return $claims;
}




$metadataQuery = '';
$classid = -1;

$responseJSON = "{}";

if(isset($_GET['action'])){
    $action = $_GET['action'];
    $respond = [];
    $objQuery = null;
    if($action == 'getByCollection'){
        if(isset($_GET['collectionID'])){
            $objQuery = new Query('',-1,$_GET['collectionID'],'',-1);
        }
    }elseif($action = 'getItem' ){
        if(isset($_GET['swallowID'])){
            $objQuery = new Query('',-1,-1,'',$_GET['swallowID']);    
        }
    }
    
    if(!is_null($objQuery)){
        $responseJSON = $objQuery->executeLD();
        $responseArray = json_decode($responseJSON,true);   
        foreach($responseArray as $item){
            $respond[] = generateQuickStatements(json_encode($item));
        }

        #print respond as tab separated list
        foreach($respond as $item){
            foreach($item as $claim){
                print implode("|",$claim)."||\n";
            }
        }
    }else{
        print "Error: check the parameters";
    }
    
}



?>