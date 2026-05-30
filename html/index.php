<?php
require_once realpath(__DIR__ . "/../vendor/autoload.php");
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

    <!-- Font and Bootstrap import -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400&display=swap"
    rel="stylesheet"
    />
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
    crossorigin="anonymous"
    />
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
    />

    <link href="./stylesheets/main.css" rel="stylesheet">
</head>
<body>
    <h1>Random Movie Finder</h1>
    <div id="main-content" class="container">
        <form action="" method="GET">
            <div class="row justify-content-center">
                <div class="col-lg-2 col-md-6 col-10 form-item">
                    <label for="obscurity">Obscurity</label><br />
                    <input type="range" id="obscurity" name="obscurity" <?php if(isset($_REQUEST['submit'])) {echo "value=" . $_GET['obscurity'];} else {echo "value=5";} ?> min="0" max="10">
                </div>
            </div>
            <div class="row justify-content-center">
                <input type="submit" name="submit" value="Get Movie" class="col-lg-2 col-md-6 col-10 form-item">
            </div>
        </form>
        <br />
        <br />
        <div class="row justify-content-center">
        <?php
        if (isset($_REQUEST['submit'])) {
            $complete = false;
            while (!$complete) {
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
                    echo "<br />" . $movie['title'];
                    $complete = true;
                    }
                    }
                    }
                    ?>
        </div>
    </div>
        <!-- Bootstrap Import -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"
        ></script>
</body>
</html>