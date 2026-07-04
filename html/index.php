<?php
require realpath(__DIR__ . "/../movieCard.php");
require realpath(__DIR__ . "/../movieProviders.php");
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

    <link href="./stylesheets/main.css?v=202607031438" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Random Movie Finder</h1>
    </header>
    <div id="main-content" class="container">
        <form action="" method="POST">
            <div class="row justify-content-center">
                <div id="genre-dropdown-container" class="col-lg-2 col-md-6 col-10">
                    <div class="dropdown w-100">
                        <button id="genre-dropdown" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Genre
                        </button>
                        <div class="dropdown-menu w-100">
                            <ul class="dropdown-list">
                                <?php
                                // Generate list of all genres as dropdown items
                                $genreList = array(
                                "Action"=>"28"
                                ,"Adventure"=>"12"
                                ,"Animation"=>"16"
                                ,"Comedy"=>"35"
                                ,"Crime"=>"80"
                                ,"Drama"=>"18"
                                ,"Family"=>"10751"
                                ,"Fantasy"=>"14"
                                ,"History"=>"36"
                                ,"Horror"=>"27"
                                ,"Music"=>"10402"
                                ,"Mystery"=>"9648"
                                ,"Romance"=>"10749"
                                ,"Science Fiction"=>"878"
                                ,"TV Movie"=>"10770"
                                ,"Thriller"=>"53"
                                ,"War"=>"10752"
                                ,"Western"=>"37");
                                echo "<li class=\"dropdown-item\"><label class=\"w-100\"><input type=\"checkbox\" checked name=\"select-all\" id=\"all-genres-checked\"> Select All</label></li>";
                                // check if checkbox should be checked
                                foreach ($genreList as $g => $c) {
                                    $checked = true;
                                    if (isset($_REQUEST['submit'])) {
                                        if (isset($_POST['genres'])) {
                                            if (! in_array($c, $_POST['genres'])) {
                                                $checked = false;
                                            }
                                        } else {
                                            $checked = false;
                                        }
                                    }
                                    echo "<li class=\"dropdown-item\"><label class=\"w-100 genre-label\"><input type=\"checkbox\"";
                                    if ($checked) {
                                        echo " checked ";
                                    }
                                    echo "name=\"genres[]\" value=\"$c\"> $g</label></li>";
                                //<li class="dropdown-item"><label class="w-100 genre-label"><input type="checkbox" checked name="genres[]" value="code"> Genre</label></li>
                                }
                                ?>
                            </ul>
                            <script>
                                checkSelectAll();
                                // stop clicks inside list closing the dropdown
                                document.querySelectorAll('.genre-label').forEach(label => {
                                    label.addEventListener('click', function(e) {
                                        checkSelectAll();
                                        e.stopPropagation(); 
                                    });
                                });

                                document.querySelectorAll('.dropdown-menu').forEach(label => {
                                    label.addEventListener('click', function(e) {
                                        e.stopPropagation(); 
                                    });
                                });


                                // check if all are selected
                                function checkSelectAll() {
                                    items = document.getElementsByName("genres[]");
                                    allChecked = true;
                                    for (i = 0; i < items.length; i++) {
                                        if (! items[i].checked) {
                                            allChecked = false;
                                            break;
                                        }
                                    }
                                    if (allChecked) {
                                        document.getElementById("all-genres-checked").checked = true;
                                    } else {
                                        document.getElementById("all-genres-checked").checked = false;
                                    }
                                }

                                document.getElementById("all-genres-checked").addEventListener('click', function() {
                                    check = false;
                                    if (document.getElementById("all-genres-checked").checked) {
                                        check = true;
                                    }
                                    items = document.getElementsByName("genres[]");
                                    for (i = 0; i < items.length; i++) {
                                        items[i].checked = check;
                                    }
                                });
                            </script>
                    </div>
                </div>
                </div>
            </div>
                <div class="row justify-content-center">
                    <div class="col-lg-2 col-md-6 col-10 form-item">
                        <label for="obscurity">Obscurity <span class="circled-question">?<div class="tooltip-box">This is an estimate of how well known the movie is.<br />Set slider to the centre for the "most random" result</div></span></label><br />
                    <input type="range" id="obscurity" name="obscurity" <?php if(isset($_REQUEST['submit'])) {echo "value=" . $_POST['obscurity'];} else {echo "value=5";} ?> min="0" max="10">
                </div>
            </div>
            <div class="row justify-content-center">
                <input type="submit" name="submit" value="Get Movie" class="col-lg-2 col-md-6 col-10 form-item">
            </div>
        </form>
        <br />
        <br />
        <div class="row justify-content-center g-0">
        <?php
        if (isset($_REQUEST['submit'])) {
            $complete = false;
            if (!isset($_POST['genres'])) {
                echo "please select at least 1 genre";
            } else {
                while (!$complete) {
                    $token = $_ENV['TMDB_READ_ACCESS_TOKEN'];
                    $url = "https://api.themoviedb.org/3/discover/movie?";
                    
                    // add genre
                    $genres = $_POST['genres'];
                    $genre = $genres[rand(0, (sizeof($genres) - 1))];
                    $url = $url . "with_genres=$genre";

                    // add obscurity (number of reviews)
                    switch ($_POST['obscurity']) {
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
                        echo "<br /><div class='col'>";
                        echo getMovieCard($movie, "GB");
                        echo "</div>";
                        $providersUrl = "https://api.themoviedb.org/3/movie/" . $movie['id'] . "/watch/providers";
                        curl_setopt($curl, CURLOPT_URL, $providersUrl);
                        $providers = curl_exec($curl);
                        if (curl_errno($curl)) {
                            echo "cURL error: " . curl_errno($curl);
                            exit;
                        }
                        $providers = json_decode($providers, true);
                        $streamingProvidersImages = displayStreamingProviders($providers['results'], 'GB');
                        $rentalProvidersImages = displayRentalProviders($providers['results'], 'GB');
                        $purchaseProvidersImages = displayPurchaseProviders($providers['results'], 'GB');
                        echo "<br /><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . "GB" . "' target='_blank'>";
                        echo "<p style='display: inline-block'>Stream: </p>";
                        echo $streamingProvidersImages;
                        echo "</div><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . "GB" . "' target='_blank'>";
                        echo "<p style='display: inline-block'>Rent: </p>";
                        echo $rentalProvidersImages;
                        echo "</div><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . "GB" . "' target='_blank'>";
                        echo "<p style='display: inline-block'>Buy: </p>";
                        echo $purchaseProvidersImages;
                        echo "</a></div>";
                        echo "<br /><a class=\"row mx-auto jw-link\" href=\"https://www.justwatch.com/\" target=\"_blank\"><p class=\"m-0\" style=\"display: inline-block; text-align: center;\">Powered By <img src=\"./images/JustWatch.svg\" alt=\"JustWatch\" height=\"15pt\"></p></a>";
                        $complete = true;
                    }
                }
            }
        }
                    ?>
        </div>
    </div>
    <footer>
        <p>This website uses <a href="https://www.themoviedb.org/" target="_blank"><img src="./images/TMDB.svg" alt="TMDB" height="15pt"></a> and the <a href="https://developer.themoviedb.org" target="_blank">TMDB APIs</a> but is not endorsed, certified, or otherwise approved by <a href="https://www.themoviedb.org/" target="_blank"><img src="./images/TMDB.svg" alt="TMDB" width="30pt"></a>. 
        Streaming data powered by <a href="https://www.justwatch.com/" target="_blank"><img src="./images/JustWatch.svg" alt="JustWatch" height="15pt"></a></p>
        <p>View more of my projects at <a href="https://www.ben-robertson.com/projects/">ben-robertson.com</a>.</p>
    </footer>
        <!-- Bootstrap Import -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"
        ></script>
</body>
</html>
<!-- This website uses <TMDBLogo> and the TMDB APIs<link to api> but is not endorsed, certified, or otherwise approved by <TMDBLogo>. -->
<!-- Streaming data powered by <JustWatchLogo> -->