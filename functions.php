<?php
function includeScripts(array $scripts) {
    $scriptTags = '';
    foreach ($scripts as $script) {
        $scriptTags .= "<script src='$script'></script>\n";
    }
    return $scriptTags;
}

function formatPrice(int $rawPrice) {
    $actualPrice = ceil($rawPrice);

    if ($actualPrice >= 1000) {
        $actualPrice = number_format($actualPrice, 0, '', ' ');
    }
    return $actualPrice . ' &#8381;';
}

function getExpirationDate($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    $hours = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad(floor(($diff % 3600) / 60), 2, "0", STR_PAD_LEFT);

    return [$hours, $minutes];
}

function createDetailProducts(array $products) {
    $detailProducts = [];

    foreach ($products as $product) {
        list($hours, $minutes) = getExpirationDate($product['expiration']);

        $product['hours'] = $hours;
        $product['minutes'] = $minutes;
        $product['isNew'] = $hours < 1;

        $detailProducts[] = $product;
    }
    return $detailProducts;
}
