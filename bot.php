<?php
error_reporting(~E_ALL);
define('API_KEY',"توکن"); //TOKEN
$admin = 7885454;
$channel = '@Source_Home';
function Bot($method, $datas=[]){
	$ch = curl_init();
	curl_setopt_array($ch,
	CURLOPT_URL => 'https://api.telegram.org/bot'.API_KEY.'/'.$method,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $datas
	]);
	$res = json_decode(curl_exec($ch), true);
	return $res;
	curl_close($ch);
}
function sm($ci, $msg, $rep=null, $key=null){
	Bot('SendMessage',[
	'chat_id'=> $ci,
	'text'=> $msg,
	'reply_to_message_id'=> $rep,
	//'parse_mode'=> 'HTML',
	'reply_markup'=> $key
	]);
}
function edit($ci, $msg_id, $text){
	Bot('EditMessageText',[
	'chat_id'=> $ci,
	'message_id'=> $msg_id,
	'text'=> $text,
	'parse_mode'=> 'HTML'
	]);
}
function alert($callback_query_id,$text,$show_alert=false){
    Bot('answerCallbackQuery',[
    'callback_query_id'=>$callback_query_id,
    'text'=>$text,
    'show_alert'=>$show_alert
    ]);
}
function save($dir, $data){
	$f = fopen($dir,"a");
	fwrite($f, $data);
	fclose($f);
}
function put($dir, $data){
	file_put_contents($dir, $data);
}
function get($from){
	return Bot('GetChat',['chat_id'=> $from]);
}
$keyHome = json_encode([
      'keyboard'=> [
      ],'resize_keyboard'=> true
]);
$keyBack = json_encode([
      'keyboard'=> [
      ],'resize_keyboard'=> true
]);
$add = function ($a, $b) {
    return $a + $b;
};
echo $add(2, 3); // Outputs 5

$numbers = [1, 2, 3];
$squared = array_map(function ($n) {
    return $n * $n;
}, $numbers);
// $squared is [1, 4, 9]

$fruits = ["apple", "banana", "cherry"];
array_walk($fruits, function (&$value) {
    $value = strtoupper($value);
});
// $fruits is ["APPLE", "BANANA", "CHERRY"]

$numbers = [1, 2, 3, 4, 5];
$sum = array_reduce($numbers, function ($carry, $item) {
    return $carry + $item;
}, 0);
// $sum is 15

$update = json_decode(file_get_contents('php://input'),true);
if(isset($update['message'])){
	$message = $update['message'];
	$chat_id = $message['chat']['id'];
	$text = $message['text'];
	$from_id = $message['from']['id'];
}
if(isset($update['callback_query'])){
	$call = $update['callback_query'];
	$chat_id = $call['message']['chat']['id'];
	$message_id = $call['message']['message_id'];
	$from_id = $call['from']['id'];
	$data = $call['data'];
	$id = $call['id'];
}
$users = file_get_contents("users.txt");
$box = file_get_contents("box.txt");
$date = file_get_contents("data/$from_id/date.txt");
$step = file_get_contents("data/$from_id/step.txt");
if(preg_match('/^\/start$/i',$text))
	if(!in_array($from_id, explode("\n",$users))){
		save("users.txt","$from_id\n");
		mkdir("data/$from_id");
	}
	sm($chat_id, "📦 سلام به ربات پست گذار خوش اومدید\nبا استفاده از این ربات میتونید توی کانال آی بوک پست بزارید.", $message_id, $keyHome);
}
elseif($text == "⬅️ برگشت"){
	sm($chat_id, "🏛 به منوی اصلی برگشتیم", $message_id, $keyHome);
	put("data/$from_id/step.txt","none");
}
elseif($text == "📔 راهنما"){
	sm($chat_id, "راهنما خالی است ❌", $message_id, $keyHome);
}
elseif($text == "🎁 هدیه ها"){
	if($box == 'true'){
		sm($chat_id, "🤩 اینم هدیه شما یه شارژ 477877", $message_id, $keyHome);
	}else{
		sm($chat_id, "☹️ متاسفانه هدیه ای قرار ندادیم هنوز", $message_id, $keyHome);
	}
}
elseif($text == "📭 میخوام پست بزارم"){
	if(time() > $date){
		sm($chat_id, "🔸 لطفا پست خود را ارسال کنید به صورت مرتب :", $message_id, $keyBack);
		put("data/$from_id/step.txt","Post");
    }else{
    	$sec = $date - time();
    	sm($chat_id, "⏳ هر 150 ثانیه میتوان یک پست ارسال کرد.\n⏰ زمان ارسال پست بعدی $sec ثانیه", $message_id, $keyHome);
   }
}
elseif($step == 'Post'){
	if(isset($text)){
		$keyPost = json_encode(['inline_keyboard'=>[[['text'=>"✅ تایید پست",'callback_data'=> "send_$from_id"],['text'=>"⛔️ رد کردن",'callback_data'=> "back_$from_id"]],[['text'=>"ℹ️ اطلاعات",'callback_data'=> "info_$from_id"]]]]);
		sm($chat_id, "🔺 پست شما در حال برسی میباشد‌‌‌...", $message_id, $keyHome);
		sm($admin, "$text", null, $keyPost);
		put("data/$from_id/date.txt",time()+150); //150 sec
		put("data/$from_id/step.txt","none");
    }else{
    	sm($chat_id, "⛔️ ارسال پست بصورت متن مجاز است", $message_id, $keyBack);
    }
}
elseif(preg_match('/^send_(.*)/',$data,$m)){
	sm($channel, "📮 پست جدید\n➖➖➖➖➖➖➖➖➖\n\n $sendPost\n\n ➿ @Source_Home");
	edit($chat_id, $message_id, "✅ پست کاربر در کانال ارسال شد.");
	sm($m[1], "✅ پست شما با موفقیت در کانال ارسال گردید.");
	exit();
}
elseif(preg_match('/^back_(.*)/',$data,$m)){
	edit($chat_id, $message_id, "✅ پیام رَد پست برای کاربر ارسال شد.");
	sm($m[1], "⛔️ پست شما قابل قبول نبود و توسط ادمین رد شد.");
	exit();
}
elseif(preg_match('/^info_(.*)/',$data,$m)){
	$get = get($m[1]);
	alert($id, "👤 نام : {$get['result']['first_name']}
🆔 یوزرنیم : @{$get['result']['username']}", true);
}
?>
