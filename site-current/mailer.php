<?php

// Visitor-supplied bytes must never reach a mail header. Two independent
// barriers guard the one value that is allowed anywhere near one: first every
// line terminator and null byte is removed, then what remains must survive
// PHP's own address validator. The removal runs first on purpose, so the bytes
// that get approved are byte-for-byte the bytes that get written out — there is
// no window in which the checked string and the emitted string differ.
if (!function_exists('torin_header_safe')) {
    function torin_header_safe($value) {
        $value = (string) $value;
        $value = str_replace(array("\r", "\n", "\0"), '', $value);
        return trim($value);
    }
}

// Each of the four reads is guarded twice: the key may be absent, and it may
// arrive as an array (a bracketed field name in the posted body), which would
// make the escaping call below raise a type error on a modern PHP. Anything
// that error printed would be output sent before the redirect, which would
// break the redirect. The guards are what keep the empty-bodied redirect intact
// under hostile input.
 $from1 = (isset($_POST['name']) && is_string($_POST['name'])) ? $_POST['name'] : '';
 $email1 = (isset($_POST['mail']) && is_string($_POST['mail'])) ? $_POST['mail'] : '';
 $mobile1 = (isset($_POST['mobile']) && is_string($_POST['mobile'])) ? $_POST['mobile'] : '';
 $question1 = (isset($_POST['message']) && is_string($_POST['message'])) ? $_POST['message'] : '';
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

// The reply address is the only submitted value permitted to influence a
// header, and only once it has cleared both barriers. If it clears neither, no
// such header is emitted at all rather than a repaired guess: a visitor who
// mistypes their address still has it shown as text in the message body, so a
// genuine enquiry is answerable by hand instead of being silently mailed to
// whatever the typo happened to spell.
$reply_to = '';
$reply_candidate = torin_header_safe($email1);
if ($reply_candidate !== '' && filter_var($reply_candidate, FILTER_VALIDATE_EMAIL) !== false) {
    $reply_to = $reply_candidate;
}

// The sender identity is a fixed literal on the shop's own domain with no
// interpolation of any kind — nothing a visitor types can reach it.
$headers =  "From: TORIN.bg Message Form <office@torin.bg>\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
if ($reply_to !== '') {
    $headers .= "Reply-To: " . $reply_to . "\r\n";
}

mail("office@torin.bg","Въпрос към ТОРИН КОМПЮТЪРС, изпратен през www.TORIN.bg. ",$m2,$headers);


    header("Location: msg.html");



?>
