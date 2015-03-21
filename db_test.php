<?php
include_once('database.php');
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>Database test</title>
    </head>
    <body>
    <h1>Práce s databázou - advanced</h1>
<?php
$records = Database::select_all('test');

foreach ($records as $record) {
    foreach ($record as $value) {
        $text = $value;
        if ($value instanceof DateTime)
            $text = $value->format('d. m. Y');
        echo $text . " | ";
    }
    echo "<br>";
}
?>

    </body>
</html>