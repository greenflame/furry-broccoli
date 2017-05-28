<?php

  // DB

  function db_connect() {
    $servername = "progiot.ddns.net:3306";
    $username = "greenflame";
    $password = "qwerty123";
    $dbname = "moustached_search";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");

    if ($conn->connect_error) {
        die('Error: ' . $conn->connect_error);
    }

    return $conn;
  }

  function db_dispose($conn) {
    $conn->close();
  }

  function db_check_document_exists($conn, $url) {
    $sql = "SELECT * FROM `Document` WHERE `url` = '{$url}';";
    $res = $conn->query($sql);

    return $res->num_rows !== 0;
  }

  function db_insert_document($conn, $url, $content) {
    $sql = "INSERT INTO `Document` (`url`, `content`) VALUES ('{$url}', '{$content}');";

    if ($conn->query($sql) !== TRUE) {
      die('Error: ' . $conn->error);
    }

    return $conn->insert_id;
  }

  function db_insert_entry($conn, $doc_id, $term, $count) {
    $sql = "INSERT INTO `Entry` (`document_id`, `term`, `count`) VALUES ({$doc_id}, '{$term}', {$count});";

    if ($conn->query($sql) !== TRUE) {
      die('Error: ' . $conn->error);
    }

    return $conn->insert_id;
  }

  // Indexer

  function group_by_occurrences($arr) {
    $dict = [];
    foreach ($arr as $i) {
      $dict[$i]++;
    }
    return $dict;
  }

  function smart_split($str) {
    $arr = mb_strtolower($str, 'utf-8');
    return preg_split("#[^\p{L}]+#u", $arr, -1, PREG_SPLIT_NO_EMPTY);
  }

  function count_terms($doc) {
    $terms_arr = smart_split($doc);
    return group_by_occurrences($terms_arr);
  }

  function engine_consume($conn, $url, $doc) {
    if (db_check_document_exists($conn, $url)) {
      die('Document already indexed');
    }

    $doc_id = db_insert_document($conn, $url, $doc);
    $dict = count_terms($doc);

    foreach ($dict as $term => $cnt) {
      db_insert_entry($conn, $doc_id, $term, $cnt);
    }
  }

  function engine_search($conn, $phrase) {

  }

  //-------

  $conn = db_connect();
  // echo db_insert_document($conn, 'oo', 'c');
  // var_dump(db_check_document_exists($conn, "uss"));
  engine_consume($conn, 'first url', 'asd кот кто кот? это Кот..');
  db_dispose($conn);

  // $dict = [];
  // $doc1 = "asd кот кто кот? это Кот..";
  // var_dump(count_terms($doc1));
?>
