<?php

/**
 * Alternative to helper method url().
 * getUrl returns the url according to the frontend url.
 *
 * @param string $path
 * @return string
 */
function getUrl(string $path)
{
    return config('app.frontend_url') . $path;
}

function namify($input){
    # Replace non-alpha numeric with space
    $match = '/[^A-Za-z0-9]+/';
    $replace = ' ';
    $input = preg_replace($match, $replace, $input);

    return ucwords(strtolower($input));
}

function sluggify($url)
{
    # Prep string with some basic normalization
    $url = strtolower($url);
    $url = strip_tags($url);
    $url = stripslashes($url);
    $url = html_entity_decode($url);

    # Remove quotes (can't, etc.)
    $url = str_replace('\'', '', $url);

    # Replace non-alpha numeric with hyphens
    $match = '/[^a-z0-9]+/';
    $replace = '-';
    $url = preg_replace($match, $replace, $url);

    $url = trim($url, '-');

    return $url;
}
