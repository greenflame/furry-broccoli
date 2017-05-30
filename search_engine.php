<?php

// DB

function db_connect()
{
    $server_name = "progiot.ddns.net:3306";
    $username = "greenflame";
    $password = "qwerty123";
    $db_name = "moustached_search";
    $conn = new mysqli($server_name, $username, $password, $db_name);
    $conn->set_charset("utf8");
    if ($conn->connect_error) {
        throw new Exception('Connection error: ' . $conn->connect_error);
    }

    return $conn;
}

function db_close($conn)
{
    $conn->close();
}

function db_truncate($conn)
{
    $sql = 'DELETE FROM `Document`;';
    if ($conn->query($sql) !== TRUE) {
        throw new Exception('Error: ' . $conn->error);
    }
}

function db_check_if_document_exists($conn, $url)
{
    $sql = "SELECT * FROM `Document` WHERE `url` = '{$url}';";
    $res = $conn->query($sql);
    return $res->num_rows !== 0;
}

function db_insert_document($conn, $url, $text)
{
    $sql = "INSERT INTO `Document` (`url`, `content`) VALUES ('{$url}', '{$text}');";
    if ($conn->query($sql) !== TRUE) {
        throw new Exception('Error: ' . $conn->error);
    }

    return $conn->insert_id;
}

function db_get_documents($conn) {
    $sql = "SELECT * FROM Document ORDER BY `url` DESC;";
    $result = $conn->query($sql);
    $ret = [];
    while ($row = $result->fetch_assoc()) {
        array_push($ret, $row);
    }

    return $ret;
}

function db_insert_entry($conn, $doc_id, $term, $count)
{
    $sql = "INSERT INTO `Entry` (`document_id`, `term`, `count`) VALUES ({$doc_id}, '{$term}', {$count});";
    if ($conn->query($sql) !== TRUE) {
        throw new Exception('Error: ' . $conn->error);
    }

    return $conn->insert_id;
}

function db_perform_search($conn, $query)
{
    $terms = join("|", smart_split($query));
    $sql = "SELECT * FROM (SELECT `document_id`, SUM(`count`) + COUNT(*) * 1000 AS `score` FROM `Entry` WHERE
            `term` REGEXP '^({$terms}).*$' GROUP BY `document_id`) AS `Rank` JOIN `Document` ON
            `Document`.`id` = `Rank`.`document_id` ORDER BY `score` DESC;";
    $result = $conn->query($sql);
    $ret = [];
    while ($row = $result->fetch_assoc()) {
        array_push($ret, $row);
    }

    return $ret;
}

// Indexation

function group_by_occurrences($arr)
{
    $dict = [];
    foreach($arr as $i) {
        if (isset($dict[$i])) {
            $dict[$i]++;
        }
        else {
            $dict[$i] = 1;
        }
    }

    return $dict;
}

function smart_split($str)
{
    $str = preg_replace('/[^\p{L}\s\d]/u', '', $str);
    $arr = mb_strtolower($str, 'utf-8');
    $ret = preg_split("/[\s]+/u", $arr, -1, PREG_SPLIT_NO_EMPTY);
    return $ret;
}

function count_terms($text)
{
    $terms_arr = smart_split($text);
    return group_by_occurrences($terms_arr);
}

// Interface

function engine_consume($conn, $url, $text)
{
    if (db_check_if_document_exists($conn, $url)) {
        throw new Exception('Document already indexed');
    }

    $doc_id = db_insert_document($conn, $url, $text);
    $dict = count_terms($text);

    $errors = 0;

    foreach($dict as $term => $cnt) {
        try {
            db_insert_entry($conn, $doc_id, $term, $cnt);
        }
        catch(Exception $e) {
            $errors++;
        }
    }
}

function engine_search($conn, $phrase)
{
    return db_perform_search($conn, $phrase);
}

?>
