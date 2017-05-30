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

// Indexer

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

function engine_consume($conn, $url, $text)
{
    if (db_check_if_document_exists($conn, $url)) {
        throw new Exception('Document already indexed');
    }

    $doc_id = db_insert_document($conn, $url, $text);
    $dict = count_terms($text);
    foreach($dict as $term => $cnt) {
        try {
            db_insert_entry($conn, $doc_id, $term, $cnt);
        }

        catch(Exception $e) {
            // echo "Can't insert entry: {$term}. {$e->getMessage() }<br />";
        }
    }
}

function engine_search($conn, $phrase)
{
    return db_perform_search($conn, $phrase);
}

// -------

// $cat = 'Ко́шка, или дома́шняя ко́шка (лат. Félis silvéstris cátus), — домашнее животное, одно из наиболее популярных[1] (наряду с собакой) «животных-компаньонов»[2][3][4]. С зоологической точки зрения домашняя кошка — млекопитающее семейства кошачьих отряда хищных. Ранее домашнюю кошку нередко рассматривали как отдельный биологический вид. С точки зрения современной биологической систематики домашняя кошка (Felis silvestris catus) является подвидом лесной кошки (Felis silvestris)[5]. Являясь одиночным охотником на грызунов и других мелких животных, кошка — социальное животное[6], использующее для общения широкий диапазон звуковых сигналов, а также феромоны и движения тела[7]. В настоящее время в мире насчитывается около 600 млн домашних кошек[8], выведено около 200 пород, от длинношёрстных (персидская кошка) до лишённых шерсти (сфинксы), признанных и зарегистрированных различными фелинологическими организациями. На протяжении 10 000 лет кошки ценятся человеком, в том числе за способность охотиться на грызунов и других домашних вредителей[9][10].';
// $dog = 'Соба́ка (лат. Canis lupus familiaris) — домашнее животное, одно из наиболее распространённых (наряду с кошкой) «животных-компаньонов». Первоначально домашняя собака была выделена в отдельный биологический вид (лат. Canis familiaris) Линнеем в 1758 году, в 1993 году реклассифицирована Смитсоновским институтом и Американской ассоциацией териологов в подвид волка (Canis lupus)[2]. С зоологической точки зрения, собака — плацентарное млекопитающее отряда хищных семейства псовых. Собаки известны своими способностями к обучению, любовью к игре, социальным поведением. Выведены специальные породы собак, предназначенные для различных целей: охоты, охраны, тяги гужевого транспорта и др., а также декоративные породы (например, болонка, пудель). При необходимости разграничения по полу употребляются термины «кобе́ль» (самец) и «су́ка» (самка).';
//
//
// echo 'Connecting to DB... ';
// $conn = db_connect();
// echo 'ok<br />';
//
// echo 'Truncating DB... ';
// db_truncate($conn);
// echo 'ok<br />';
//
// echo 'Indexing cat document... ';
// engine_consume($conn, 'http://кошка.com', $cat);
// echo 'ok<br />';
//
// echo 'Indexing dog document... ';
// engine_consume($conn, 'http://собака.ru', $dog);
// echo 'ok<br />';
//
// echo 'Performing test query: "кошка домашнее и"... ';
// $res = engine_search($conn, 'кошка домашнее и');
// echo 'ok<br />';
// var_dump($res);
//
// echo 'Closing connection... ';
// db_close($conn);
// echo 'ok<br />';

?>
