<?

// Настройки подключения к базе
$mysql_name = "redirector";
$mysql_host = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_table = "redirector_links";

// Подключение к базе
$mysql_connection = @mysqli_connect($mysql_host, $mysql_user, $mysql_password);
if (!$mysql_connection) {
    echo 'MySQL connection error: ' . mysql_error();
    exit;
} else {
    mysqli_select_db($mysql_connection, $mysql_name);

    // Автоинсталлятор. После установки скрипта строчки можно закомментировать
    /* ------8<------ линия отреза ------8<------ */
    $query = "CREATE TABLE IF NOT EXISTS `" . $mysql_table . "` (
        `link_id`    INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID ссылки в базе',
        `link_hash`  VARCHAR(32) COMMENT 'Хэш ссылки',
        `link_url`   TEXT COMMENT 'Адрес ссылки',
        PRIMARY KEY (`link_id`),
        KEY `link_hash` (`link_hash`)
    ) ENGINE=MyISAM /*!40101 DEFAULT CHARSET=utf8 COMMENT='Re:Director Database' */";

    mysqli_query($mysql_connection, $query);
    if (mysqli_error($mysql_connection)) {
        echo 'Can\'t create database: ' . mysqli_error($mysql_connection);
        exit;
    }
}

/** Отрисовка основного контента
 * @param string $content
 */
function write_page($content = '')
{
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta content="text/html; charset=utf-8" http-equiv="content-type">
    <meta http-equiv="content-language" content="ru">
    <title>redirector</title>
    <style type="text/css">
    * {
        margin: 0px;
        padding: 0px;
    }
    body {
        font-family: Arial;
    }
    a {
        text-decoration: none;
        color: #0000FF;
    }

    </style>
</head>
<body>
<div style="text-align:center; padding: 30px 0 20px 0;">
';
    echo $content;

    echo '<form action="/" method="post">
<input type="hidden" name="do" value="add">
<input type="text" style="width:350px; font-size:20px;" name="url" value="">
<input type="submit" style="font-size:20px; width:150px;" value="Создать">
</form>
</div>
</body>
</html>';
}

/** Функция получения кода ссылки из индекса
 * @param $id
 * @return string
 */
function getLink($id)
{
    $digits = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $link = '';
    do {
        $dig = $id % 62;
        $link = $digits[$dig] . $link;
        $id = floor($id / 62);
    } while ($id != 0);
    return $link;
}

/** Функция получения индекса из кода ссылки
 * @param $link
 * @return float|int
 */
function getIndexLink($link)
{
    $digits = array(
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        'a' => 10,
        'b' => 11,
        'c' => 12,
        'd' => 13,
        'e' => 14,
        'f' => 15,
        'g' => 16,
        'h' => 17,
        'i' => 18,
        'j' => 19,
        'k' => 20,
        'l' => 21,
        'm' => 22,
        'n' => 23,
        'o' => 24,
        'p' => 25,
        'q' => 26,
        'r' => 27,
        's' => 28,
        't' => 29,
        'u' => 30,
        'v' => 31,
        'w' => 32,
        'x' => 33,
        'y' => 34,
        'z' => 35,
        'A' => 36,
        'B' => 37,
        'C' => 38,
        'D' => 39,
        'E' => 40,
        'F' => 41,
        'G' => 42,
        'H' => 43,
        'I' => 44,
        'J' => 45,
        'K' => 46,
        'L' => 47,
        'M' => 48,
        'N' => 49,
        'O' => 50,
        'P' => 51,
        'Q' => 52,
        'R' => 53,
        'S' => 54,
        'T' => 55,
        'U' => 56,
        'V' => 57,
        'W' => 58,
        'X' => 59,
        'Y' => 60,
        'Z' => 61
    );
    $id = 0;
    for ($i = 0; $i < strlen($link); $i++) {
        $id += $digits[$link[(strlen($link) - $i - 1)]] * pow(62, $i);
    }
    return $id;
}

// Переход по ссылке
if (isset($_GET['link'])) {
    $link = trim($_GET['link']);
    if ($link) {
        $link_id = getIndexLink($link);
        $query = "SELECT * FROM `" . $mysql_table . "` WHERE `link_id`='" . $link_id . "'";
        $sql_result = mysqli_query($mysql_connection, $query);
        $row = mysqli_fetch_array($sql_result, MYSQLI_ASSOC);
        if (isset($row['link_url'])) {
            // Переадресация заголовками
            Header('Location: ' . $row['link_url']);
            // Переадресация HTML
            $content = '<meta http-equiv="Refresh">';
            // Переадресация JavaScript
            // Ручной переход
            $content .= '<a href="' . htmlspecialchars(
                    $row['link_url']
                ) . '">Нажмите сюда для перехода по запрошенной ссылке</a><br><br>';
            write_page($content);
        } else {
            $content = '<div style="font-size:16px; color:#FF0000;">Ошибка! Запрошенная ссылка не найдена</div><br>';
            write_page($content);
        }
    } else {
        $content = '<div style="font-size:16px; color:#FF0000;">Произошла неизвестная ошибка</div><br>';
        write_page($content);
    }
} // Добавление ссылки
elseif (isset($_POST['do']) && $_POST['do'] == 'add') {
    if (isset($_POST['url'])) {
        $link_url = trim($_POST['url']);
        if ($link_url) {
            if (!preg_match('#^[a-z]{3,}\:#', $link_url)) {
                $link_url = 'http://' . $link_url;
            }

            // Проверить, есть ли такая ссылка в базе
            $link_hash = md5($link_url);
            $query = "SELECT * FROM `" . $mysql_table . "` WHERE `link_hash`='" . $link_hash . "' LIMIT 1";
            $sql_result = mysqli_query($mysql_connection, $query);
            $row = mysqli_fetch_array($sql_result, MYSQLI_ASSOC);
            // Такая ссылка уже есть
            if (isset($row['link_id'])) {
                $link_short = getLink($row['link_id']);
            } // Добавить ссылку в базу
            else {
                $query = "INSERT INTO `" . $mysql_table . "` SET
                    `link_hash`='" . $link_hash . "',
                    `link_url`='" . mysqli_real_escape_string($mysql_connection, $link_url) . "'";
                mysqli_query($mysql_connection, $query);

                $query = "SELECT LAST_INSERT_ID() AS `link_id`";
                $sql_result = mysqli_query($mysql_connection, $query);
                $row = mysqli_fetch_array($sql_result, MYSQLI_ASSOC);
                $link_short = getLink($row['link_id']);
            }

            // HTTP или HTTPS
            if (isset($_SERVER['HTTP_SCHEME'])) {
                $scheme = strtolower($_SERVER['HTTP_SCHEME']);
            } else {
                if ((isset($_SERVER['HTTPS']) && strtolower(
                            $_SERVER['HTTPS']
                        ) != 'off') || $_SERVER['SERVER_PORT'] == 443) {
                    $scheme = 'https';
                } else {
                    $scheme = 'http';
                }
            }

            // Отрисовать страницу с контентом
            $content = '<br>Добавлена ссылка: ';
            $content .= '<input type="text" style="width:350px; font-size:20px;" value="' . $scheme . '://' . getenv(
                    'HTTP_HOST'
                ) . '/' . $link_short . '"><br><br>';
            write_page($content);
        } else {
            // Ссылка не передана, переход на главную страницу
            Header('Location: /');
            exit;
        }
    } else {
        // Неправильные параметры, переход на главную страницу
        Header('Location: /');
        exit;
    }
} // Главная страница
else {
    write_page();
}
