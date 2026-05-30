<?php
require_once realpath(__DIR__ . "../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Movie Finder</title>
</head>
<body>
    <h1>Random Movie Finder</h1>
    <form action="" method="GET">
        <label for="obscurity">Obscurity</label><br />
        <input type="range" id="obscurity" name="obscurity" <?php if(isset($_REQUEST['submit'])) {echo "value=" . $_GET['obscurity'];} else {echo "value=5";} ?> min="0" max="10">
        <input type="submit" name="submit">
    </form>
        <?php
        if (isset($_REQUEST['submit'])) {
            $complete = false;
            while (!$complete) {
                echo "<br />";
                $token = $_ENV['TMDB_READ_ACCESS_TOKEN'];
                $url = "https://api.themoviedb.org/3/discover/movie?";
                
                // add genre
                $genres = array(28, 12, 16, 35, 80, 18, 10751, 14, 36, 27, 10402, 9648, 10749, 878, 10770, 53, 10752, 37);
                $genre = $genres[rand(0, (sizeof($genres) - 1))];
                $url = $url . "with_genres=$genre";

                // add obscurity (number of reviews)
                switch ($_GET['obscurity']) {
                    case 0:
                        $obscurity = "&vote_count.gte=5000";
                        break;
                    case 1:
                        $obscurity = "&vote_count.gte=3000";
                        break;
                    case 2:
                        $obscurity = "&vote_count.gte=1500";
                        break;
                    case 3:
                        $obscurity = "&vote_count.gte=750";
                        break;
                    case 4:
                        $obscurity = "&vote_count.gte=300";
                        break;
                    case 5:
                        $obscurity = "&vote_count.gte=150";
                        break;
                    case 6:
                        $obscurity = "&vote_count.gte=150&vote_count.lte=3000";
                        break;
                    case 7:
                        $obscurity = "&vote_count.gte=100&vote_count.lte=1000";
                        break;
                    case 8:
                        $obscurity = "&vote_count.gte=100&vote_count.lte=500";
                        break;
                    case 9:
                        $obscurity = "&vote_count.gte=50&vote_count.lte=150";
                        break;
                    case 10:
                        $obscurity = "&vote_count.gte=20&vote_count.lte=50";
                        break;
                }
                $url = $url . $obscurity;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $token",
                    'accept: application/json'
                ]);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                $response = curl_exec($curl);
                if (curl_errno($curl)){
                    echo "cURL error: " . curl_error($curl);
                    exit;
                }
                $data = json_decode($response, true); 
                $max = ceil($data['total_results']/20.0);
                // limit max at 500 (api restriction)
                if ($max > 500) {
                    $max = 500;
                }
                $page = 1;
                if ($max > 1) {
                    $page = rand(1, $max);
                }
                $url = $url . "&page=$page";
                curl_setopt($curl, CURLOPT_URL, $url);
                $response = curl_exec($curl);
                if (curl_errno($curl)) {
                    echo "cURL error: " . curl_errno($curl);
                    exit;
                }
                $data = json_decode($response, true);
                if (sizeof($data['results']) > 1) {
                    $movieIndex = rand(0, (sizeof($data['results']) - 1));
                    $movie = $data['results'][$movieIndex];
                    echo $movie['title'];
                    $complete = true;
                }
            }
        }
        ?>
</body>
</html>