<form method="post">
    <label>Длинна массива рандомного</label>
    <input type="number" name="fieldsCount" required
           value="<?= isset($_POST['fieldsCount']) ? $_POST['fieldsCount'] : '' ?>">
    <label>Требуемая сумма</label>
    <input type="number" name="sum" required value="<?= isset($_POST['sum']) ? $_POST['sum'] : '' ?>">
    <button type="submit">Submit</button>
</form>

<?php
if (!empty($_POST['sum']) && !empty($_POST['fieldsCount'])) {
    $sum = (int)$_POST['sum'];
    $stackFields = [];
    for ($i = 1; $i <= (int)$_POST['fieldsCount']; $i++) {
        $stackFields[] = $i;
    }
    var_dump(checkNumbers($stackFields, $sum));
} elseif (empty($_POST['fieldsCount']) || empty($_POST['sum'])) {
    echo "Укажите правильные значения";
}

function checkNumbers(array $checkNumbers, int $sum)
{
    $count = count($checkNumbers);
    for ($i = 0; $i < ($count - 1); $i++) {
        $number = array_shift($checkNumbers);
        foreach ($checkNumbers as $numeric) {
            if (($number + $numeric) == $sum) {
                return true;
            }
        }
    }
    return false;
}

?>