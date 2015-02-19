<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>CSFD - API</title>
		<style>
		body {
			font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
			font-size: 13px;
		}
		</style>
	</head>
	<body>
		<form action="" method="get">
			<input type="text" name="id" id="id"/>
			<button type="submit"></button>
		</form>
	</body>
</html>

<?php
include 'global.php';


// FILM

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sessionid = isset($_GET['sessionid']) ? $_GET['sessionid'] : "";
$user = isset($_GET['user']) ? $_GET['user'] : "";
$password = isset($_GET['password']) ? $_GET['password'] : "";

if(!$id){ exit; }

logAction('FILM: '.$id);
$film_html = getUrl('http://www.csfd.cz/film/'.$id, $sessionid, $user);
$html = str_get_html($film_html);

// INFO

$info = $html->find('.info', 0);
$nazev_cz = strip_tags( trim( $info->find('h1', 0)->innertext ) );
$nazev_orig = trim( @$info->find('h3', 0)->innertext );
$zanr = $info->find('.genre', 0)->innertext;
$zeme = $info->find('.origin', 0)->innertext;
$zeme = str_replace("&#039;", "'", $zeme);

foreach($info->find('div') as $tvurci_html){
  $tvurci_array = csfdFilmTvurci($tvurci_html);
  $tvurci_typ = $tvurci_array['typ'];
  $tvurci[$tvurci_typ] = $tvurci_array['tvurci'];
}

$rating = csfdFilmRating( $html->find('#rating .average', 0)->innertext );

$obsah = trim( strip_tags( @$html->find('#plots li', 0)->plaintext ) );
$obsah = str_replace('&nbsp;', ' ', $obsah);
$obsah = str_replace('&', '&amp;', $obsah);

$obrazek = trim( $html->find('#poster img', 0)->src );

$trailer_class = $html->find('.videos', 0)->class;
$trailer = strstr($trailer_class, "disabled") ? 0 : 1;

$galerie_class = $html->find('.photos', 0)->class;
$galerie = strstr($galerie_class, "disabled") ? 0 : 1;

// KOMENTARE

$komentare = null;
$i=0;
$komentare_html = $html->find('.ui-posts-list', 0);
if($komentare_html){
  foreach($komentare_html->find('li') as $komentar_html){  $i++;
    $komentar_dom = str_get_html($komentar_html);
    $komentare[$i]['jmeno'] = $komentar_dom->find('a', 0)->plaintext;
    $komentare[$i]['id'] = csfdId($komentar_dom->find('a', 0)->href);
    $text = $komentar_dom->find('p.post', 0)->plaintext;
    //$text = htmlspecialchars($text);
    $text = str_replace("&", "&amp;", $text);
    $komentare[$i]['text'] = $text;

    $rating_e = $komentar_dom->find('.rating', 0);
    if($rating_e){
      $rating_star = intval( strlen( @$komentar_dom->find('img.rating', 0)->alt ) );
    }else{
      $rating_star = null;
    }
    $komentare[$i]['rating'] = $rating_star;
  }
}

// TOKEN
$token = @$html->find("#my-rating input[name=_token_]", 0)->value;

// DELETE TOKEN
$delete_link = @$html->find("#my-rating .private", 0)->href;
preg_match("@token=(.+)&@", $delete_link, $delete_parts);
$delete_token = isset($delete_parts[1]) ? $delete_parts[1] : null;

// MY RATING
$mystars = $html->find("#my-rating .my-rating img");
$myrating = count($mystars);
if($myrating==0){
  $isodpad = @$html->find("#my-rating .rating", 0)->plaintext;
  if($isodpad=="odpad!"){$myrating=0;}else{$myrating='';}
}

// LOGIN
$login = @csfdId( @$html->find("#user-menu a", 0).href );

// relogin
if(!$login && $sessionid && $password){
  $logintext = file_get_contents($dirpath."login.php?user=$user&password=$password");
  $loginxml = new SimpleXMLElement($logintext);
  $sessionid = (string) $loginxml->sessionid;
  header("location:$dirpath"."film.php?id=$id&user=$user&password=$password&sessionid=$sessionid");
  exit;
}

echo $nazev_cz;
echo '<br>';
			echo $nazev_orig;
echo '<br>';
			echo $rating;
echo '<br>';
			echo $obsah;
echo '<br>';
			echo $zanr;
echo '<br>';
			echo $zeme;

?>
