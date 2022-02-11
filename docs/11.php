<?php
if (isset($_GET['hangup'])) {exit();}
$id = $_GET['ApiEnterID'];
$id = preg_replace('/[^0-9.]+/', '', $id);
$phone = $_GET['fax'];



//מה להחזיר לשרת
$mahale = file_get_contents("https://call2all.co.il/ym/api/SendFax?token=033065522:038181&pdfFile=/45/$id.pdf&phone=$id");
 //print_r ($mahale);
//להמיר את זה לפורמט
$mahale = json_decode($mahale,true);
 
//עכשיו מכיון שאנחנו מקבלים בתשובה מימות המשיח עוד כמה נתונים , נשלוף מתוך התשובה את הנתונים שאנחנו צריכים (אישור העלאה)
$mahale = $mahale ["responseStatus"];

if
($mahale = "OK")
{

 echo "id_list_message=f-000.d-$phone";
}
else
{
 echo  "id_list_message=f-001";

}

?>
