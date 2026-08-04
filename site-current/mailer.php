<?php

 $from1 = $_POST['name'];
 $email1 = $_POST['mail'];
 $mobile1 = $_POST['mobile'];
 $question1 = $_POST['message'];
 $from = htmlentities($from1,ENT_QUOTES,'UTF-8');
 $email = htmlentities($email1,ENT_QUOTES,'UTF-8');
 $mobile = htmlentities($mobile1,ENT_QUOTES,'UTF-8');
 $question = htmlentities($question1,ENT_QUOTES,'UTF-8');
 $ddddd = date("\изпратено на j.m.Y \в H:i:s");

 $m2 = '
      <html>
      <head>
      <title>Question Form</title>
      </head>
      <body>
      <p align="center"><u>Въпрос към ТОРИН КОМПЮТЪРС, изпратен през www.TORIN.bg</u></p>
	  <p align="center">'.$ddddd.'</p>
      <table align="center" border="1">
          <tr>
            <th align="center" width="100" style="padding: 10px;
            background-color: cornflowerblue;
            font-size: 18px;
            color: white;
            border: 0;">Име</th>
            <th align="center" width="100" style="padding: 10px;
            background-color: cornflowerblue;
            font-size: 18px;
            color: white;
            border: 0;">E-mail</th>
            <th align="center" width="100" style="padding: 10px;
            background-color: cornflowerblue;
            font-size: 18px;
            color: white;
            border: 0;">Телефонен номер</th>
            <th align="center" width="300" style="padding: 10px;
            background-color: cornflowerblue;
            font-size: 18px;
            color: white;
            border: 0;">Съобщение</th>
          </tr>
          <tr>
            <td align="center" style="padding: 10px;
            background-color: orange;
            color: white;
            font-size: 16px;
            font-weight: 400;">'.$from.'</td>
            <td align="center" style="padding: 10px;
            background-color: orange;
            color: white;
            font-size: 16px;
            font-weight: 400;">'.$email.'</td>
            <td align="center" style="padding: 10px;
            background-color: orange;
            color: white;
            font-size: 16px;
            font-weight: 400;">'.$mobile.'</td>
            <td align="center" style="padding: 10px;
            background-color: orange;
            color: white;
            font-size: 16px;
            font-weight: 400;">'.$question.'</td>
          </tr>
          <br>
      </table>
      </body>
      </html>
       ';

// $headers =  "From: TORIN.bg Message Form <office@torin.bg>\r\n";
// $headers .= "Content-type: text/html; charset=cp1251\r\n";
//$headers .= "MIME-Version: 1.0\r\n";
// mail("office@torin.bg","TORIN.bg Message Form",$m2,$headers);

//     $file1 = $_SERVER['HTTP_HOST'];
//     $file2 = dirname($_SERVER['PHP_SELF']);
//     $file3 = $file1 . $file2 ."/"."sendmsg.php?status=sent";
//     header("Location: http://" . $file3);


$headers =  "From: <$email>\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";

mail("office@torin.bg","Въпрос към ТОРИН КОМПЮТЪРС, изпратен през www.TORIN.bg. ",$m2,$headers);


    // //  $file1 = $_SERVER['HTTP_HOST'];
    // //  $file2 = dirname($_SERVER['PHP_SELF']);
    // //  $file3 = $file1 . $file2 ."/"."index.html";
    // //  $succ = 'index.html'
    // //  header("Location: http://" . $succ);
    // echo "Using header() function;
    header("Location: msg.html");



?>
