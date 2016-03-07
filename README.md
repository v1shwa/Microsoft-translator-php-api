# Microsoft Translator API - PHP

----------

A simple PHP wrapper for Microsoft Translator API v2

###Usage

Before getting started, register an application at [Microsoft Azure Marketplace](https://datamarket.azure.com/developer/applications) and save the 'Client Id' and 'Client Secret' obtained there.

####Initialize:

    <?php
    include 'src/MicrosoftTranslator.php';
	
	$client_id = 'YOUR-CLIENT-ID';
	$client_secret = 'YOUR-CLIENT-SECRET';
	$mth = new MicrosoftTranslator($client_id,$client_secret);
    ?>

####Translate

    $text    = 'Hello World';
    $to_lang = 'it';
    echo $mth->translate($text,$to_lang);
    // Outputs: Salve, mondo
 - Optionally, you could provide a 3rd parameter, from language.

	$from_lang = 'en';
	echo $mth->translate($text,$to_lang,$from_lang);    

####Detect Language

    $text    = 'Hello World';
    echo $mth->detect($text);
    // Outputs: en

####List Supported Languages

    echo $mth->getSupportedLangs();
    // Outputs: array of language codes

