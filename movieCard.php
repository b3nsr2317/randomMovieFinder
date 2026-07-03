<?php 
function getMovieCard($movie, $region) {
    // containing div
    $result = "<div class='mx-auto' id='movie-card' style='background-image: url(\"https://image.tmdb.org/t/p/w500" . $movie['poster_path'] . "\");' aria-label='Movie Poster: " . $movie['title'] . "'>";
    // link to tmdb
    $result = $result . "<a id='card-link' class='w-100 h-100' href='https://www.themoviedb.org/movie/" . $movie['id'] . "/watch?locale=" . $region . "' target='_blank'>";
    // overlay div
    $result = $result . "<div id='card-overlay' class='d-flex align-items-center justify-content-center'><h2>";
    $result = $result . $movie['title'] . " - " . substr($movie['release_date'], 0, 4);
    $result = $result . "</h2>";
    $result = $result . "<img class='click-indicator align-self-right' src='./images/link.png' alt='pointer cursor'>";
    $result = $result . "</div>";
    $result = $result . "</a>";
    $result = $result . "</div>";
    return $result;
}
?>