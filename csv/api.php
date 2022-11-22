<?php
include("./db.php");

header('Content-Type:application/json');

$sql="SELECT * FROM `coun` JOIN `01-currencies` ON `coun`.`currency_code`=`01-currencies`.`iso_code` ";
$res=mysqli_query($conn,$sql);
$row=mysqli_fetch_all($res,MYSQLI_ASSOC);
echo "<pre>";
print_r($row);

?>