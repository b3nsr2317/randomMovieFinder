<?php
require realpath(__DIR__ . "/../movieCard.php");
require realpath(__DIR__ . "/../movieProviders.php");
require realpath(__DIR__ . "/../searchList.php");
require_once realpath(__DIR__ . "/../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$gtag = $_ENV['GTAG_ID'];

$token = $_ENV['TMDB_READ_ACCESS_TOKEN'];

$region = "GB";

// Start session, if session is older than 30 minutes unset the session
session_start();
if (!isset($_SESSION["sessionStart"])) {
    $_SESSION["sessionStart"] = microtime(true);
} elseif ((microtime(true) - $_SESSION["sessionStart"])/60000000 > 30) {
    session_unset();
}

// unless a session already exists with current region, get list of services
if (isset($_SESSION["servicesRegion"])) {
    if (! ($_SESSION["servicesRegion"] == $region)) {
        $_SESSION["services"] = getServices($region, $token);
        }
} else {
    $_SESSION["services"] = getServices($region, $token);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($gtag)) { ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php $gtag; ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo $gtag; ?>');
    </script>
    <?php } ?>
    <title>Random Movie Finder</title>
    
    <meta name="description" content="Find a random movie to watch instantly. Stop endlessly scrolling through Netflix and get a tailored film choice instantly using the genre and streaming service filters.">

    <!-- Open Graph Social Sharing Tags -->
    <meta property="og:title" content="Random Movie Finder">
    <meta property="og:description" content="Filter movies by genre and streaming service to find something to watch instantly.">
    <meta property="og:url" content="https://projects.ben-robertson.com/movie-picker/">
    <meta property="og:type" content="website">

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

    <link href="./stylesheets/main.css?v=202607071318" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Random Movie Finder</h1>
    </header>
    <div id="main-content" class="container">
        <form action="" id="main-form" method="POST">
            <!-- container for genre selector -->
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
                                // select/deselect all checkbox
                                echo "<li class=\"dropdown-item\"><label class=\"w-100\"><input type=\"checkbox\" checked name=\"select-all\" id=\"all-genres-checked\"> Select All</label></li>";
                                // check if each checkbox should be checked
                                foreach ($genreList as $g => $c) {
                                    // default to checked
                                    $checked = true;
                                    // if no post request, or genre not included in post request, set to unchecked
                                    if (isset($_REQUEST['submit'])) {
                                        if (isset($_POST['genres'])) {
                                            if (! in_array($c, $_POST['genres'])) {
                                                $checked = false;
                                            }
                                        } else {
                                            $checked = false;
                                        }
                                    }
                                    // list item for each genre
                                    echo "<li class=\"dropdown-item\"><label class=\"w-100 genre-label\"><input type=\"checkbox\"";
                                    // if required, set to checked
                                    if ($checked) {
                                        echo " checked ";
                                    }
                                    echo "name=\"genres[]\" value=\"$c\"> $g</label></li>";
                                //<li class="dropdown-item"><label class="w-100 genre-label"><input type="checkbox" checked name="genres[]" value="code"> Genre</label></li>
                                }
                                ?>
                            </ul>
                            <script>
                                checkGenreSelectAll();
                                // stop clicks inside list closing the dropdown
                                document.querySelectorAll('.genre-label').forEach(label => {
                                    label.addEventListener('click', function(e) {
                                        checkGenreSelectAll();
                                        e.stopPropagation(); 
                                    });
                                });

                                // check if all genres are selected
                                function checkGenreSelectAll() {
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
                <div id="services-dropdown-container" class="col-lg-2 col-md-6 col-10">
                    <div class="dropdown w-100">
                        <button id="services-dropdown" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Streaming Services
                        </button>
                        <div class="dropdown-menu w-100">
                            <ul class="dropdown-list">
                                <?php
                                function getServices($region, $token) {
                                    // get providers for region
                                    $_SESSION["servicesRegion"] = $region;
                                    $providersUrl = "https://api.themoviedb.org/3/watch/providers/movie?watch_region=" . $region;
                                    $providersCurl = curl_init();
                                    curl_setopt($providersCurl, CURLOPT_URL, $providersUrl);
                                    curl_setopt($providersCurl, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($providersCurl, CURLOPT_HTTPHEADER, [
                                        "Authorization: Bearer $token",
                                        'accept: application/json'
                                        ]);
                                        curl_setopt($providersCurl, CURLOPT_CUSTOMREQUEST, 'GET');
                                        $response = curl_exec($providersCurl);
                                        if (curl_errno($providersCurl)){
                                            echo "cURL error: " . curl_error($providersCurl);
                                            exit;
                                            }
                                    $data = json_decode($response, true);
                                    $services = array_values($data['results']);
                                    return $services;
                                }
                                $services = $_SESSION["services"];
                                $serviceList = array();
                                for ($i = 0; $i < sizeof($services); $i++) {
                                    $serviceList += [$services[$i]['provider_name'] => $services[$i]['provider_id']];
                                }
                                // Generate list of all services as dropdown items
                                echo "<li class=\"dropdown-item\"><label class=\"w-100\"><input type=\"checkbox\" name=\"select-all\" id=\"all-services-checked\"> Select All</label></li>";
                                // check if checkbox should be checked
                                foreach ($serviceList as $s => $c) {
                                    $checked = true;
                                    if (isset($_REQUEST['submit'])) {
                                        if (isset($_POST['services'])) {
                                            if (! in_array($c, $_POST['services'])) {
                                                $checked = false;
                                            }
                                        } else {
                                            $checked = false;
                                        }
                                    } else {
                                        $checked = false;
                                    }
                                    echo "<li class=\"dropdown-item\"><label class=\"w-100 service-label\"><input type=\"checkbox\"";
                                    if ($checked) {
                                        echo " checked ";
                                    }
                                    echo "name=\"services[]\" value=\"$c\"> $s</label></li>";
                                //<li class="dropdown-item"><label class="w-100 service-label"><input type="checkbox" checked name="services[]" value="code"> Service</label></li>
                                }
                                ?>
                            </ul>
                            <script>
                                checkServicesSelectAll();
                                // stop clicks inside list closing the dropdown
                                document.querySelectorAll('.service-label').forEach(label => {
                                    label.addEventListener('click', function(e) {
                                        checkServicesSelectAll();
                                        e.stopPropagation(); 
                                    });
                                });

                                // check if all are selected
                                function checkServicesSelectAll() {
                                    items = document.getElementsByName("services[]");
                                    allChecked = true;
                                    for (i = 0; i < items.length; i++) {
                                        if (! items[i].checked) {
                                            allChecked = false;
                                            break;
                                        }
                                    }
                                    if (allChecked) {
                                        document.getElementById("all-services-checked").checked = true;
                                    } else {
                                        document.getElementById("all-services-checked").checked = false;
                                    }
                                }

                                document.getElementById("all-services-checked").addEventListener('click', function() {
                                    check = false;
                                    if (document.getElementById("all-services-checked").checked) {
                                        check = true;
                                    }
                                    items = document.getElementsByName("services[]");
                                    for (i = 0; i < items.length; i++) {
                                        items[i].checked = check;
                                    }
                                });
                                
                                document.querySelectorAll('.dropdown-menu').forEach(label => {
                                    label.addEventListener('click', function(e) {
                                        e.stopPropagation(); 
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <span class="circled-question">?<div class="tooltip-box">If none are selected, streaming provider filter not used<br />On high obscurities, this can take some time</div></span>
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
            // get new movie
            $complete = false;
            if (!isset($_POST['genres'])) {
                echo "please select at least 1 genre";
            } else {
                $call_counter = 0;
                while (!$complete) {
                    // initialise URL string
                    $url = "https://api.themoviedb.org/3/discover/movie?";
                    
                    // add genre(s)
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
                    
                    // make API call
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
                    //increment call counter
                    $call_counter += 1;

                    // decode result and get the number of pages of results
                    $data = json_decode($response, true); 
                    $max = ceil($data['total_results']/20.0);
                    // limit max at 500 (api restriction)
                    if ($max > 500) {
                        $max = 500;
                        }
                    // pick a random page within the limit
                    $page = 1;
                    if ($max > 1) {
                        $page = rand(1, $max);
                        }
                        $url = $url . "&page=$page";
                    // make second API call to get that page of results
                    curl_setopt($curl, CURLOPT_URL, $url);
                    $response = curl_exec($curl);
                    if (curl_errno($curl)) {
                        echo "cURL error: " . curl_errno($curl);
                        exit;
                    }
                    $data = json_decode($response, true);
                    $pageSize = sizeof($data['results']);
                    if ($pageSize > 1) {
                        $increment = 0;
                        // pick random movie from page and store it in its own variable
                        $movieIndex = rand(0, (sizeof($data['results']) - 1));
                        // loop through page until movie with selected watch providers is found
                        while ($increment < $pageSize) {
                            if ($increment + $movieIndex < $pageSize) {
                                $movie = $data['results'][$movieIndex + $increment];
                            } else {
                                $movie = $data['results'][$increment];
                            }
                            // call API to get providers
                            $providersUrl = "https://api.themoviedb.org/3/movie/" . $movie['id'] . "/watch/providers";
                            curl_setopt($curl, CURLOPT_URL, $providersUrl);
                            $providers = curl_exec($curl);
                            $providers = json_decode($providers, true);
                            if (curl_errno($curl)) {
                                echo "cURL error: " . curl_errno($curl);
                                exit;
                            }
                            // if no providers selected, choose current movie
                            if (!isset($_POST['services'])) {
                                $complete = true;
                                break;
                            } else {
                                // compare selected providers to movie providers
                                $streamProviderIds = array();
                                if (isset($providers['results'][$region]['flatrate'])) {
                                    foreach($providers['results'][$region]['flatrate'] as $provider) {
                                        array_push($streamProviderIds, $provider['provider_id']);
                                    }
                                }
                                if (isset($providers['results'][$region]['ads'])) {
                                    foreach($providers['results'][$region]['ads'] as $provider) {
                                        array_push($streamProviderIds, $provider['provider_id']);
                                    }
                                }
                                if (isset($providers['results'][$region]['free'])) {
                                    foreach($providers['results'][$region]['free'] as $provider) {
                                        array_push($streamProviderIds, $provider['provider_id']);
                                    }
                                }
                                if (sizeof($streamProviderIds) > 0) {
                                    if (searchList($streamProviderIds, $_POST['services'])) {
                                        $complete = true;
                                        break;
                                    } else {
                                        $increment += 1;
                                    }
                                } else {
                                    $increment += 1;
                                }
                            }
                        }
                        if ($complete) {
                            // output movie card
                            echo "<br /><div class='col'>";
                            echo getMovieCard($movie, $region);
                            echo "</div>";
                            // display streaming provider icons
                            $streamingProvidersImages = displayStreamingProviders($providers['results'], $region);
                            $rentalProvidersImages = displayRentalProviders($providers['results'], $region);
                            $purchaseProvidersImages = displayPurchaseProviders($providers['results'], $region);
                            echo "<br /><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . $region . "' target='_blank'>";
                            echo "<p style='display: inline-block'>Stream: </p>";
                            echo $streamingProvidersImages;
                            echo "</div><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . $region . "' target='_blank'>";
                            echo "<p style='display: inline-block'>Rent: </p>";
                            echo $rentalProvidersImages;
                            echo "</div><div class='row providers-container'><a href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . $region . "' target='_blank'>";
                            echo "<p style='display: inline-block'>Buy: </p>";
                            echo $purchaseProvidersImages;
                            echo "</a></div>";
                            echo "<br /><a class=\"row mx-auto jw-link\" href=\"https://www.justwatch.com/\" target=\"_blank\"><p class=\"m-0\" style=\"display: inline-block; text-align: center;\">Powered By <img src=\"./images/JustWatch.svg\" alt=\"JustWatch\" height=\"15pt\"></p></a>";
                        }
                        // $complete = true;
                        // limit to a maximum of 15 pages of results checked to stop infinite search times
                        if (!$complete && $call_counter >= 15) {
                            echo "<br /><div class='col text-center'><h2>Request timed out, please try again with different filters</h2></div>";
                            $complete = true;
                        }
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