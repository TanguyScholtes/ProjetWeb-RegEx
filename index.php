<!DOCTYPE html>
<!--[if lt IE 7]>      <html lang="fr-be" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html lang="fr-be" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html lang="fr-be" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="fr-be" class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Introduction RegEx</title>
        <meta name="description" content="Intro aux RegEx - Cours de Projet Web du 14/10/2015">
    </head>
    <body>
        <h1>Introduction aux RegEx</h1>
        
        <p>Voir <a href="http://regexr.com/">RegExr.com</a> et <a href="http://regexcheatsheet.com/">RegexCheatSheet.com</a></p>
        
        <form action="index.php" method="post" enctype="multipart/form-data">
            <p>
                <label for="user_file">Entrez votre fichier .srt :</label>
                <input type="file" id="user_file" name="user_file">
            </p>
            <p>
                <label for="timelapse">Décalage à appliquer en millisecondes (ms) :</label>
                <input type="number" id="timelapse" name="timelapse">
            </p>
            <button type="submit" name="submit">Envoyer</button>
        </form>
        
<?php
/*
$subject = "<p>Je suis un paragraphe</p>";
$pattern = '/<(\/)?p>/';
$replace = "<$1div>";

preg_match_all($pattern, $subject, $matches);
echo preg_replace($pattern, $replace, $subject);

print_r($matches);
*/

$upload = $_FILES['user_file']['tmp_name'];
if($upload){
    echo "Upload réussi <br>";
}

global $timelapse;
$timelapse = $_POST['timelapse'];
//echo $timelapse;

$file = file($upload);
//print_r($file);
$file_length = count($file);

$pattern = "/(\d\d:\d\d:\d\d,\d\d\d)/";

function formatTime ( $milliseconds ) {
    
    $seconds = ($milliseconds / 1000) % 60;
    $minutes = ($milliseconds / (1000 * 60)) % 60;
    $hours = ($milliseconds / (1000 * 60 * 60)) % 24;
    $milliseconds = $milliseconds % 1000;
    
    if(count($hours) < 2) {
        $hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
    }
    if(count($minutes) < 2) {
        $minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT);
    }
    if(count($seconds) < 2) {
        $seconds = str_pad($seconds, 2, "0", STR_PAD_LEFT);
    }
    if(count($milliseconds) < 3) {
        $milliseconds = str_pad($milliseconds, 3, "0", STR_PAD_LEFT);
    }
        
    return "$hours:$minutes:$seconds,$milliseconds";
};

function add_timelapse ( $matches ) {
    global $timelapse;
    //echo "timelapse = " . $timelapse . "<br>";
    $modified_timecode = [];
    $pattern = '/:?(\d\d\d?):?/';
    
    //for each match
    $matches_number = count($matches);
    for( $i = 0 ; $i < $matches_number ; $i++ ){
    
        //transform timecode to milliseconds
        preg_match_all($pattern, $matches[$i], $regMatch);
        //print_r($regMatch);
        $hours = $regMatch[0][0];
        $minutes = $regMatch[0][1];
        $seconds = $regMatch[0][2];
        $milliseconds = $regMatch[0][3];
        
        $timecode = (((($hours * 60) + $minutes) * 60) + $seconds) * 1000 + $milliseconds;
        //echo "timecode in ms : " . $timecode . "<br>";
        
        //add wished timelapse
        $timecode += $timelapse;
        //echo "timecode in ms with timelapse added : " . $timecode . "<br>";
        
        //transform modified timecode to hh:mm:ss,sss format
        $modified_timecode[$i] = formatTime( $timecode );
    }
    //return modified and formated timecode    
    return $modified_timecode[0];
};

for( $i = 0 ; $i < $file_length ; $i++ ) {
    
    $subject = $file[$i];
    if(preg_match_all($pattern, $subject, $matches)) {
        
        $new_timecode = preg_replace_callback($pattern, 'add_timelapse', $subject);
        $file[$i] = $new_timecode;
    }
    echo $file[$i] . '<br>';
}

//print_r($file);

?>
    </body>
</html>