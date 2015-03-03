<?php
if (!isset($flagType)){
    $flagType = "all";
}

include("../parts/database.php");

//Autoflag:
//Looks for blends that have less then half of there refs from thier question link
//Query created with the help of TehShrike http://stackoverflow.com/users/201789/tehshrike
$autoFlags = $db->prepare("
SELECT `blends`.`id`,`blends`.`fileName`,`blends`.`questionLink`,`accesses`.`val`,
SUM(`accesses`.`ref` != `blends`.`questionLink` AND `accesses`.`ref` !=  '') AS invalidRefs, SUM(`accesses`.`ref` = `blends`.`questionLink`) AS validRefs,
SUM(`accesses`.`ref` != `blends`.`questionLink` AND `accesses`.`ref` !=  '') - SUM(`accesses`.`ref` = `blends`.`questionLink`) AS refGap
FROM `blends`
JOIN `accesses` ON `accesses`.`fileId` = `blends`.`id` AND `accesses`.`type` = 'view'
GROUP BY `blends`.`id`
HAVING `invalidRefs` > `validRefs`
ORDER BY `refGap` DESC
");

$autoFlags->execute();
$autoFlags = $autoFlags->fetchAll(PDO::FETCH_ASSOC);

foreach ($autoFlags as $key => $aflag)
{
    $autoFlags[$key]["val"] = 'auto-ref';
}


//var_dump($autoFlags);

//Query created with the help of TehShrike http://stackoverflow.com/users/201789/tehshrike
$files = $db->prepare("SELECT `blends`.`id`,`blends`.`fileName`,`blends`.`questionLink`,`accesses`.`val`,`accesses`.`date` FROM `blends` JOIN `accesses` ON `accesses`.`fileId` = `blends`.`id` AND `accesses`.`type` = 'flag' ORDER BY `accesses`.`date` DESC");

$files->execute();
$files = $files->fetchAll(PDO::FETCH_ASSOC);

//add autoFlag catches
?>