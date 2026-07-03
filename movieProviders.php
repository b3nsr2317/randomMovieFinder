<?php

function displayStreamingProviders($providers, $region) {
    // Check if entry exists for region code
    if (!isset($providers[$region])) {
        $result = "Not available to stream";
        return $result;
    }

    $providers = $providers[$region];
    $result = "";

    if (isset($providers['flatrate'])) {
        for ($i = 0; $i < sizeof($providers['flatrate']); $i++) {
            $provider = $providers['flatrate'][$i];
            $result = $result . "<img src='https://image.tmdb.org/t/p/w45" . $provider['logo_path'] . "' alt='". $provider['provider_name'] ."' class='provider-logo'>";
        }
        return $result;
    } else {
        return "Not available to stream";
    }
}

function displayRentalProviders($providers, $region) {
    // Check if entry exists for region code
    if (!isset($providers[$region])) {
        $result = "Not available to rent";
        return $result;
    }

    $providers = $providers[$region];
    $result = "";

    if (isset($providers['rent'])) {
        for ($i = 0; $i < sizeof($providers['rent']); $i++) {
            $provider = $providers['rent'][$i];
            $result = $result . "<img src='https://image.tmdb.org/t/p/w45" . $provider['logo_path'] . "' alt='". $provider['provider_name'] ."' class='provider-logo'>";
        }
        return $result;
    } else {
        return "Not available to rent";
    }
}

function displayPurchaseProviders($providers, $region) {
    // Check if entry exists for region code
    if (!isset($providers[$region])) {
        $result = "Not available to purchase";
        return $result;
    }

    $providers = $providers[$region];
    $result = "";

    if (isset($providers['buy'])) {
        for ($i = 0; $i < sizeof($providers['buy']); $i++) {
            $provider = $providers['buy'][$i];
            $result = $result . "<img src='https://image.tmdb.org/t/p/w45" . $provider['logo_path'] . "' alt='". $provider['provider_name'] ."' class='provider-logo'>";
        }
        return $result;
    } else {
        return "Not available to purchase";
    }
}

?>