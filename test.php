<?php
$operace_vyber = array('+', '-', '*', '/');
if (isset($_POST))
    foreach ($_POST as $name=>$value)
        $$name = $value;
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset='utf-8'>
    <link href="css/main.css" rel="stylesheet" media="all" type="text/css">
</head>
<body>
<div id="wrapper">

    <form action="?" method="post">
        <table>
            <tr>
                <td>Výška: </td>
                <td><input type="number" name="vyska" value="<?php if (isset($vyska)) echo $vyska; ?>" placeholder="cm"></td>
            </tr>
            <tr>
                <td>Váha: </td>
                <td><input type="number" name="vaha" value="<?php if (isset($vaha)) echo $vaha; ?>" placeholder="kg"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit" name="send" value="1">Odeslat</button>
                    <button type="submit" name="send" value="2">Hoď kostkou</button>
                </td>
            </tr>
        </table>
    </form>
    <?php
    $date = new DateTime('');
    echo $date->format('');
    if (isset($send) && $send == 1 && $vaha > 0 && $vyska > 0) {
        $vyska /= 100;
        $bmi = $vaha / ($vyska*$vyska);
        $bmi = number_format($bmi, 2, ',', '\'');
        echo "<hr>Vaše BMI je: $bmi<br>";

        if ($bmi < 18.5)
            echo "Podváha";
        else if ($bmi < 24.99)
            echo "Pohoda";
        else if ($bmi < 29.99)
            echo "Trochu obézní";
        else if ($bmi < 34.99)
            echo "Obézní";
        else if ($bmi < 39.99)
            echo "Už se zamysli";
        else
            echo "Hmmmm..., ty to máš marné.";
    }
    echo "<br>";
    if (isset($send) && $send == 2) {
        $random = mt_rand(1,6);
        echo "<img src='obrazky/k_$random.gif' alt='kostka s číslem $random'>";
    }

    ?>
    <h2>Kalkulačka</h2>
    <form action="?" method="post">
        <input type="number" name="calc[cislo1]" placeholder="1. číslo" value="<?php echo isset($calc['cislo1']) ? $calc['cislo1'] : ''; ?>">
        <select name="calc[operace]">
            <?php
            foreach ($operace_vyber as $oper) {
                $selected = (isset($calc) && $calc['operace'] == $oper) ? 'selected' : '';
                echo "<option value='$oper' $selected>$oper</option>";
            }
            ?>
        </select>
        <input type="number" name="calc[cislo2]" placeholder="2. číslo" value="<?php echo isset($calc['cislo2']) ? $calc['cislo2'] : ''; ?>">
        <button type="submit" name="calc[submit]">Spočti</button>
    </form><br>
    <?php
        if (isset($calc)) {
            $result = 0;
            $zeroDivision = false;

            switch ($calc['operace']) {
                case '+': $result = $calc['cislo1'] + $calc['cislo2']; break;
                case '-': $result = $calc['cislo1'] - $calc['cislo2']; break;
                case '*': $result = $calc['cislo1'] * $calc['cislo2']; break;
                case '/': $calc['cislo2'] == 0 ? $zeroDivision = true : $result = $calc['cislo1'] / $calc['cislo2']; break;
            }

            if (!$zeroDivision)
                echo "<strong>Výsledek:</strong> $result";
            else
                echo "<strong>Chyba:</strong> nulou nelze dělit.";
        }
    ?>

</div>
</body>
</html>