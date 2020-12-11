<?php

$channelAccessToken = 'b1+iOAN7SnVtA/MnznlHiMGwT5jgR9CcbCwpQUN078BYjLAB34cMDbC2LnuhgemRfAxfn/cd1vA+yVsNa6lLgfupJ59xVvvG/oAApaYMMdQQp2s7VX7lDaOmF60vOJgcU4OTyCiR97KgRM04SbHJrQdB04t89/1O/w1cDnyilFU='; // Access Token ค่าที่เราสร้างขึ้น

$request = file_get_contents('php://input');   // Get request content

$request_json = json_decode($request, true);   // Decode JSON request

foreach ($request_json['events'] as $event)
{
	if ($event['type'] == 'message') 
	{
		if($event['message']['type'] == 'text')
		{
			$text = $event['message']['text'];
			
			$texts = explode(" ", $text);
			
			if($texts[0] == "@บอท"){
				
				$reply_message .= "ฉันมีบริการให้คุณสั่งได้ ดังนี้...\n";				
				$reply_message .= "พิมพ์ว่า \"@บอท อ่านเลขมิเตอร์ไฟฟ้า\"\n";
				$reply_message .= "พิมพ์ว่า \"@บอท อ่านเลขมิเตอร์น้ำ\"\n";
				$reply_message .= "พิมพ์ว่า \"@บอท ขอสรุปการเปรียบเทียบรการใช้พลังงาน\"\r\n";
				
				
				if($text[1] == "ขอรายชื่อนิสิตทั้งหมด"){
					$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php');
				}else if($texts[1] == "ค้นหาชื่อนิสิต"){
					$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php?sid='.$texts[3]);
				}else if($texts[1] == "ขอรหัส"){
					$reply_message = mySQL_selectFTP('http://bot.kantit.com/json_select_ftp.php?sid='.$texts[4]);					
				}else{
				
				}
				
				
			else{ 
			//$reply_message = "กรุณาใช้รูปแบบคำสั่งที่ถูกต้องงงงง!!\n";
				
				
			}
			
			
			}
			
		} else {
			//$reply_message = 'ฉันได้รับ "'.$event['message']['type'].'" ของคุณแล้ว!';
		}
		
	} else {
		$reply_message = 'ฉันได้รับ Event "' . $event['type'] . '" ของคุณแล้ว!';
	}
	
	if($reply_message == null || $reply_message == ""){ $reply_message =  'ขออภัยฉันไม่สามารถตอบกลับข้อความ "'. $text . '" ของคุณ!'; }
		
	// reply message
	$post_header = array('Content-Type: application/json', 'Authorization: Bearer ' . $channelAccessToken);	
	
	$data = ['replyToken' => $event['replyToken'], 'messages' => [['type' => 'text', 'text' => $reply_message]]];	
	
	$post_body = json_encode($data);	
	
	// reply method type-1 vs type-2
	$send_result = reply_message_1('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);
	//$send_result = reply_message_2('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);	
}

function mySQL_selectAll($url)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	
	$data = "ผลลัพธ์:\r\n";
		
	foreach($result_json as $values) {
		$data .= $values["user_stuid"] . " " . $values["user_firstname"] . " " . $values["user_lastname"] . "\r\n";
	}
	
	return $data;
}

function mySQL_selectFTP($url)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	
	$data = "ผลลัพธ์:\r\n";
		
	foreach($result_json as $values) {
		$data .= $values["user_password"] . "\r\n";
	}
	
	return "รหัส FTP ของคุณคือ ".$data;
}


function reply_message_1($url, $post_header, $post_body)
{
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $post_header,
                'content' => $post_body,
            ],
        ]);
	
	$result = file_get_contents($url, false, $context);
	
	return $result;
}

function reply_message_2($url, $post_header, $post_body)
{
	$ch = curl_init($url);	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$result = curl_exec($ch);
	
	curl_close($ch);
	
	return $result;
}

?>
