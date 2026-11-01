<?php

function searchList($searchItems, $list) {
    if (sizeof($searchItems) > 1) {
        $list = array_flip($list);
        // check each item against the flipped array
        foreach($searchItems as $item) {
            if (isset($list[$item])) {
                return true;
            }
        }
    } else {
        if (linearSearch($searchItems[0], $list)) {
            return true;
        }
    }
    return false;
}

function linearSearch($item, $list) {
    foreach ($list as $listItem) {
        if ($item == $listItem) {
            return true;
        }
    }
    return false;
}

?>
