<?php

// language detection script for php 4 (probably 5)
// Copyright (c) rob, Jul 04. (probably) 
// WWW: http://techpatterns.com/forums/about120.html
// License: Unknown or no license

function lixgetlang() {
    $lang = 'rus';
    
    if ($_GET[lang] != 'rus' && $_GET[lang] != 'eng' && $_GET[lang] != 'deu') {
        if (lixlpixel_detect_lang() != 'ru') { $lang = 'eng'; }
        else { $lang = 'rus'; }
    } else {
        $lang = $_GET[lang];
    }
    
    return $lang;
}

function lixlpixel_get_env_var($Var)
{
    if(empty($GLOBALS[$Var]))
    {
        $GLOBALS[$Var]=(!empty($GLOBALS['_SERVER'][$Var]))?
        $GLOBALS['_SERVER'][$Var]:
        (!empty($GLOBALS['HTTP_SERVER_VARS'][$Var]))?
        $GLOBALS['HTTP_SERVER_VARS'][$Var]:'';
    }
}

function lixlpixel_detect_lang()
{
    // Detect HTTP_ACCEPT_LANGUAGE & HTTP_USER_AGENT.
    lixlpixel_get_env_var('HTTP_ACCEPT_LANGUAGE');
    lixlpixel_get_env_var('HTTP_USER_AGENT');

    $_AL=strtolower($GLOBALS['HTTP_ACCEPT_LANGUAGE']);
    $_UA=strtolower($GLOBALS['HTTP_USER_AGENT']);

    // Try to detect Primary language if several languages are accepted.
    foreach($GLOBALS['_LANG'] as $K)
    {
        if(strpos($_AL, $K)===0)
            return $K;
    }
    
    // Try to detect any language if not yet detected.
    foreach($GLOBALS['_LANG'] as $K)
    {
        if(strpos($_AL, $K)!==false)
            return $K;
    }
    foreach($GLOBALS['_LANG'] as $K)
    {
        if(preg_match("//[\[\( ]{$K}[;,_\-\)]//",$_UA))
            return $K;
    }

    // Return default language if language is not yet detected.
    return $GLOBALS['_DLANG'];
}

// Define default language.
$GLOBALS['_DLANG']='ru';

// Define all available languages.
// WARNING: uncomment all available languages

$GLOBALS['_LANG'] = array(
    'af', // afrikaans.
    'ar', // arabic.
    'bg', // bulgarian.
    'ca', // catalan.
    'cs', // czech.
    'da', // danish.
    'de', // german.
    'el', // greek.
    'en', // english.
    'es', // spanish.
    'et', // estonian.
    'fi', // finnish.
    'fr', // french.
    'gl', // galician.
    'he', // hebrew.
    'hi', // hindi.
    'hr', // croatian.
    'hu', // hungarian.
    'id', // indonesian.
    'it', // italian.
    'ja', // japanese.
    'ko', // korean.
    'ka', // georgian.
    'lt', // lithuanian.
    'lv', // latvian.
    'ms', // malay.
    'nl', // dutch.
    'no', // norwegian.
    'pl', // polish.
    'pt', // portuguese.
    'ro', // romanian.
    'ru', // russian.
    'sk', // slovak.
    'sl', // slovenian.
    'sq', // albanian.
    'sr', // serbian.
    'sv', // swedish.
    'th', // thai.
    'tr', // turkish.
    'uk', // ukrainian.
    'zh' // chinese.
    );

?>