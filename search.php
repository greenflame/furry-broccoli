<?php
  include 'search_engine.php';

  $conn = db_connect();

  $requestType = $_GET['type'];

  if ($requestType == 'indexer') {
    $linkToIndex = $_GET['link'];

    $pageContent = file_get_contents($linkToIndex);
    $text = parsePageContent($pageContent);
    $subLinks = getSubLinks($pageContent);

    engine_consume($conn, $linkToIndex, $text);

    echo $subLinks;
  }
  else if ($requestType == 'search') {
    $searchPhrase = $_GET['searchPhrase'];
    $res = engine_search($conn, $searchPhrase);
    echo json_encode($res);
  }

  function parsePageContent($pageContent)
  {
    preg_match_all('/<body>(.*?)<\/body>/s', $pageContent, $matches);
    $bodyContent = $matches[0];
    return preg_replace("/<.*?>/", "", $bodyContent);
  }

  function getSubLinks($pageContent)
  {
    preg_match_all('/<a href="(.*?)">/s', $pageContent, $matches);
    var_dump($matches);
  }
?>
