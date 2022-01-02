<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kontakty";
$subst = '';
$master = "https://www.edb.cz/katalog-firem/elektro-a-pocitace/";
$stranka = file_get_contents($master);
$re = '/(href=\"(http|https)\:\/\/(\w{1,63}\.){1,2}\w{1,10}((\/[a-zA-Z0-9\-]{1,63}){0,6})(\s{0}|\/[a-zA-Z0-9\-]{1,63}|\/[a-zA-Z0-9\-]{1,63}\/|\/|\/\?.\=[0-9]{1,3})\")/im';
$reedb = '/((href\=\"){1}(http|https)(\:\/\/){1}([a-zA-Z-]{1,63}\.){1,2}([a-zA-Z-]{1,10}\/){1}([a-zA-Z0-9\-\_]{1,63}\/|[a-zA-Z0-9\-\_]{1,63}|){1,4}(\?.\=[0-9]{1,4}|0{0})\")/im'; //https://regex101.com/r/924n4w/1
$remail = '/(href=\"(mailto:){1}[a-zA-Z0-9\-\.\_\+]{1,63}\@{1}[a-zA-Z0-9\-\.\_\+]{1,63}){1,3}\.[a-zA-Z0-9\-\.\_\+]{1,10}\"/im';
$tabulky = array('emaily', 'firmy', 'sources', 'urls');

function nacti($url, $searchfor, $regex, $offset, $offsetend){
    $stranka = file_get_contents($url);
    $array = preg_split("/\r\n|\n|\r/", $stranka);
    $temparr = array();
    foreach($array as $key=>$value){ //kazda radka nactene stranky
        if(strpos($value, $searchfor)>0){           //hledany text existuje
            preg_match_all($regex, $value, $matches, PREG_SET_ORDER, 0);    
            foreach($matches as $matk=>$matv){
                $temparr[] = substr($matches[$matk][0],$offset,$offsetend); //not in array - insert to an array
            }
        }
    }
    return array_values(array_unique($temparr));
}

function intodb($connection,$arrayname,$tablename,$collumnname){
    foreach($arrayname as $arraykey=>$arrayvalue){
        $sql = "INSERT IGNORE INTO ".$tablename." (create_time, update_time, ".$collumnname.") VALUES ((select current_timestamp()), (select current_timestamp()), LOWER('".$arrayvalue."'))";
        echo $sql.";<br />\r\n";
        $result = mysqli_query($connection, $sql);
    }
}

$conn = mysqli_connect($servername, $username, $password, $dbname); // Spojení k MySQL
if (!$conn) { // Kontrola spojení s MySQL
  die("Connection failed: " . mysqli_connect_error());
} else { // Připojeno k MySQL
    $urlarr = nacti("https://www.edb.cz/katalog-firem/elektro-a-pocitace/","divFixedHideMobile",$re,6,-1); //"href="www.domena.com"" - edb.cz
    $katalogarr = nacti("https://www.edb.cz/katalog-firem/elektro-a-pocitace/","divStrankovacH",$reedb,6,-1); //"href="www.domena.com"" - edb.cz
    $mailyarr = nacti("https://www.edb.cz/katalog-firem/elektro-a-pocitace/","mailto",$remail,13,-1); //"href="mailto:jmeno.prijmeno@domena.com"" - obecně
    $DB1 = intodb($conn,$urlarr,"urls", "Website");
    $DB2 = intodb($conn,$katalogarr,"sources", "Website");
    $DB3 = intodb($conn,$mailyarr,"emaily", "email");

    mysqli_close($conn); //uzavřít spojení do MySQL
}// Připojeno k MySQL
?>