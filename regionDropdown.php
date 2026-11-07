<?php

    $flagLinkTemplate = "https://flagcdn.com/w20/";

function getCountriesDropdown($region) {
    global $flagLinkTemplate;
    $countryList = json_decode(file_get_contents("../countryCodes.json"), true);
    asort($countryList);
    $dropdown = '<div class="dropdown" style="display: inline-block; padding-top: 10px; padding-right: 20px;">';
    $dropdown = $dropdown . '<button id="country-dropdown" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="' . $flagLinkTemplate . $region . '.png" id="country-icon">
    </button>';
    $dropdown = $dropdown . '<div class="dropdown-menu">';
    $dropdown = $dropdown . '<ul class="dropdown-list" style="padding-left: 0;">';
        // Generate list of all countries as dropdown items
        foreach ($countryList as $code => $country) {
            $dropdown = $dropdown . "<a href='?region=" . $code . "'><li class=\"dropdown-item\"><label class=\"w-100 country-label\" value=\"" . $code . "\">";
            $dropdown = $dropdown . "<img src=\"" . $flagLinkTemplate . $code . ".png\"> ";
            $dropdown = $dropdown . $country . "</label></li></a>";
        }
    $dropdown = $dropdown . '</ul>';
    $dropdown = $dropdown . "<script>
        document.querySelectorAll('.country-label').forEach(label => {
            label.addEventListener('click', function(e) {
                console.log(e.currentTarget.getAttribute('value'));
                document.getElementById('country-icon').src = '" . $flagLinkTemplate . "' + e.currentTarget.getAttribute('value') + '.png';
            });
        });
    </script>";
    $dropdown = $dropdown . "</div>
</div>";
return $dropdown;
}

?>