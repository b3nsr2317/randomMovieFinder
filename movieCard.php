<?php 
function getMovieCard($movie) {
    $result = "<div class='mx-auto' id='movie-card' style='background-image: url(\"https://image.tmdb.org/t/p/w500" . $movie['poster_path'] . "\");' aria-label='Movie Poster: " . $movie['title'] . "'>";
    $result = $result . "</div>";
    return $result;
}
?>