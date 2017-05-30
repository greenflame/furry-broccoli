<?php
  include 'search_engine.php';

  $conn = db_connect();

  $requestType = $_GET['type'];

  if ($requestType == 'indexer') {
    $linkToIndex = $_GET['link'];

    if (strlen($linkToIndex) > 0) {
      $pageContent = file_get_contents($linkToIndex);
      $text = parsePageContent($pageContent);
      $subLinks = getSubLinks($pageContent, $linkToIndex);

      try {
        engine_consume($conn, $linkToIndex, $text);
      } catch (Exception $e) {

      }

      echo json_encode($subLinks);
    }
  }
  else if ($requestType == 'search') {
    $searchPhrase = $_GET['searchPhrase'];
    $res = engine_search($conn, $searchPhrase);
    echo json_encode($res);
  }
  else if ($requestType == 'clear') {
    db_truncate($conn);
  }

  db_close($conn);

  function parsePageContent($pageContent)
  {
    // preg_match_all('/<body.*>(.*?)<\/body>/s', $pageContent, $matches);
    // $bodyContent = $matches[0];
    // $withoutScripts = preg_replace("/<script[\s\S]*>[\s\S]*?<\/script>/", "", $bodyContent[0]);
    $bodyContent = preg_replace("/<.*?>/u", "", $pageContent);
    $bodyContent = preg_replace('/[^\p{L}\s\d]/u', '', $bodyContent);
    return $bodyContent;
  }

  function getSubLinks($pageContent, $sourceLink)
  {
    preg_match_all('/<a href="(.*?)">/s', $pageContent, $matches);

    $subLinks = array();

    for ($i=0; $i < count($matches[1]); $i++) {
      $parsedUrl = parse_url($matches[1][$i]);
      $repairedUrl = repairUrl($parsedUrl, parse_url($sourceLink));

      if ($repairedUrl != NULL && !in_array($repairedUrl, $subLinks)) {
        array_push($subLinks, $repairedUrl);
      }
    }

    return $subLinks;
  }

  function repairUrl($url, $sourceUrl) {
    $supportedTypes = array('html', 'php');

    $host = "";
    $scheme = "";
    $path = "";

    if (array_key_exists("host", $url)) {
      $host = $url["host"];
      $scheme = array_key_exists("scheme", $url) ? $url["scheme"] : "http";
    }
    else {
      $host = $sourceUrl["host"];
      $scheme = array_key_exists("scheme", $sourceUrl) ? $sourceUrl["scheme"] : "http";
    }

    if (array_key_exists("path", $url)) {
      $path = $url["path"];

      $splitted = split('\.', $path);

      if (count($splitted) > 1 && strlen($splitted[1]) && !in_array($splitted[1], $supportedTypes)) {
        return NULL;
      }
    }

    return $scheme . '://' . $host . $path;
  }
?>
