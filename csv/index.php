<?php

include("./db.php");


function tablesname(){
    global $conn ;
$tnsql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA ='country_currency';";
$tnres=mysqli_query($conn,$tnsql);
$tnarr=[];
if($tnres){
while($tnrow=mysqli_fetch_assoc($tnres)){
    array_push($tnarr,$tnrow['TABLE_NAME']);
}
return $tnarr;
}  
else{
    return false;
}
}

$pgnum=0;


function tabledata($arr){
      
     global $pgnum;
      global $conn;
      $fulltable="";
      $acti="";
      $actn="";
    for($i=0;$i<count($arr);$i++){ 
     $tname=$arr[$i];
    //  print_r($tname);
       $actc=0;
    if(isset($_POST[$tname])&&array_keys($_POST)[0]==$tname){
        $pg=$_POST[$tname]-1;   
        $pgnum=$pg*25;
        $actc=$pg;
    }
    
    // elseif(isset($_POST["prev".$tname])&&array_keys($_POST)[0]=="prev".$tname){ 
    //     $pgnum=$pgnum-25;
    //     $actc=$actc-1;
    //     echo "prev";
    // }
    // elseif(isset($_POST["next".$tname])&&array_keys($_POST)[0]=="next".$tname){ 
    //     $pgnum=$pgnum+25;
    //     $actc=$actc+1;
    //     echo "next";
    // }
    else{
        $pgnum=0;
    }
    

    $tnumsql="SELECT * FROM `".$tname."`;";
      $tnumres=mysqli_query($conn,$tnumsql);
      $tnumrows=mysqli_num_rows($tnumres);

      $pagenum=ceil($tnumrows/25);


      $tdsql="SELECT * FROM `".$tname."`LIMIT ".$pgnum.",25;";
      $tdres=mysqli_query($conn,$tdsql);
      $tdrow=mysqli_fetch_all($tdres,MYSQLI_ASSOC);
    //    echo "<pre>";
    //  print_r($tdrow);
    
     $tdhsql="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tname' ;";
     $tdhres=mysqli_query($conn,$tdhsql);
     $tdhrow=mysqli_fetch_all($tdhres,MYSQLI_ASSOC);
     $tablehead="";
     for($l=0;$l< count($tdhrow);$l++){
        $tablehead.= '<th scope="col">'.$tdhrow[$l]["COLUMN_NAME"].'</th>';
     }
     $tabledata="<tr>";
     for($k=0;$k<count($tdrow);$k++){
       for($j=0;$j<count($tdhrow);$j++){
         $tabledata.=  '<td>'.$tdrow[$k][$tdhrow[$j]["COLUMN_NAME"]].'</td>' ;
       }
       $tabledata.='</tr>';
     }

     $pagelink="";
     $pn=1;
     for($p=0;$p<$pagenum;$p++){
     
       $v=$p+1;
       $act="";
       if($actc==$p){
         $act="active";
       }
       $pagelink.='<button type="submit" name="'.$tname.'" value='.$v.' class="page-link pagb " '.$act.'  >'.$pn.'</button>';
       $pn++;
     }
    
     if($actc===0){
        $acti="disabled";
     }
     else{
        $acti="";
     
    }
     if($actc==$pagenum-1){
        $actn="disabled";
     }

     else{
        $actn="";
     }
       
    //  print_r($tdhrow);
    $fulltable.= '<div class="col-12" >
    <h3 class="my-5">'.$tname.' Table</h3>
    <table class="table table-striped">
     <thead>
       <tr>
        '.$tablehead.'
       </tr>
     </thead>

     <tbody>
       '.$tabledata.'
     </tbody>
   </table>
   <nav aria-label="Page navigation example">
  <ul class="pagination  justify-content-center">
  <form method="post" class="d-flex">
    <li class="page-item ">
        <button type="submit" name="prev'.$tname.'" value="prev" class="page-link pagb " '.$acti.' >Previous</button>
    </li>
    
    <li class="page-item d-flex">
  
   '.$pagelink.'
    <li class="page-item">
    <button type="submit" name="next'.$tname.'" value="next" class="page-link pagb " '.$actn.' >Next</button>
    </li>
    </form>
  </ul>
</nav>
   </div>
   '
   ;
    }
    return $fulltable;

}





function querycreate($qarray){
$Iquery="";
for($i=0;$i<count($qarray);$i++){

    if($i==count($qarray)-1){
        $Iquery.="`".$qarray[$i]."`";
    }
else{
    $Iquery.="`".$qarray[$i]."`,";
}
}
return $Iquery;

}


function queryvalue($qarray){
    $Iquery="";
    for($i=0;$i<count($qarray);$i++){
    
        if($i==count($qarray)-1){
            $Iquery.="'".$qarray[$i]."'";
        }
    else{
        $Iquery.="'".$qarray[$i]."',";
    }
    }
    return $Iquery;
    
    }



if(isset($_FILES['csv']['name'])&& $_FILES['csv']['error']==0){
$fname=$_FILES['csv']['name'];
$tname=$_FILES['csv']['tmp_name'];
$fdata=fopen($tname,'r');
move_uploaded_file($tname,'./files/'.$fname);
$fhead=fgetcsv($fdata);
// print_r($fhead);


$hquery="";
for($i=0;$i<count($fhead);$i++){
if($i==count($fhead)-1){
    $hquery.=("`".$fhead[$i]."` TEXT DEFAULT NULL");
}
else{
    $hquery.=("`".$fhead[$i]."` TEXT DEFAULT NULL,");
}
}
 
$leng=strlen($fname)-4;

$tablename=substr($fname,0,$leng);


$sqlcheck="SELECT * FROM `".$tablename."`";
$rescheck=mysqli_query($conn,$sqlcheck);



if($rescheck){
         
    $tempname=$tablename.rand(10,100);
    $sql1="CREATE TABLE `".$tempname."` (
        `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,".$hquery."
    );";
    
    $res1=mysqli_query($conn,$sql1);
    
    if ($res1) {

        while($fdata2=fgetcsv($fdata)){

            $sql2="INSERT INTO `".$tempname."`(". querycreate($fhead).") VALUES (".queryvalue($fdata2).")";
            $res2=mysqli_query($conn,$sql2);
        }
        // print_r(tablesname());
        // echo"Add successfully";
      } else {
        echo "Error creating table: " . mysqli_error($conn);
      }

}


else{

    $sql1="CREATE TABLE `".$tablename."` (
        `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,".$hquery."
    );";
    
    $res1=mysqli_query($conn,$sql1);
    
    if ($res1) {
 
        while($fdata2=fgetcsv($fdata)){

            $sql2="INSERT INTO `".$tablename."`(". querycreate($fhead).") VALUES (".queryvalue($fdata2).")";
            $res2=mysqli_query($conn,$sql2);
        }
        // print_r(tablesname());
        // echo"Add successfully";

       
      } else {
        echo "Error creating table: " . mysqli_error($conn);
      }

}


} 

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CSV Upload</title>
    <!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>
<style>
    .pagb{
        background-color: black !important;
        color: white !important;
    }
    .pagb:hover{
        background-color: whitesmoke !important;
        color: black !important;
        
    }
    button:disabled,button[disabled]{
        color: gray !important;
        background-color: #212529 !important;
    }
    button:active,button[active]{
        background-color: whitesmoke !important;
        color: black !important;
    }

</style>
<body>
<div class="container-fluid">
    <div class="row justify-content-center bg-light">
     <div class="col-9">
        <h1 class="text-center " >CSV Uploader</h1>
     </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <div class="row justify-content-center ">
        <div class="col-5 text-center">
            <img src="https://source.unsplash.com/1600x900/?black-and-white-texture" class="img-fluid  shadow  mb-5 bg-body rounded" alt="">
        </div>
        <div class="col-4" style="align-self: center;">
        <div class="mb-3">
        <form method="post"  enctype="multipart/form-data">
     <input class="form-control" type="file" id="formFile"  name="csv" value="">
      <div class="d-grid mx-auto">
      <button type="submit" class="btn btn-dark  mt-2" name="submit">upload</button>
      </div> 
        </form>
       </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center text-center">
<?php  
$tbname=tablesname();

echo tabledata($tbname);
?>
    </div>
</div>
   

</body>
</html>