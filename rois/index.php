<?php
//--------------------------------------------------
//　おえかきけいじばん「ROIS」
//　by sakots & OekakiBBS reDev.Team  https://dev.oekakibbs.net/
//--------------------------------------------------

//スクリプトのバージョン
define('ROIS_VER','v0.4.1'); //lot.210809.1

//設定の読み込み
require(__DIR__.'/config.php');
require(__DIR__.'/templates/'.THEMEDIR.'/template_ini.php');

//phpのバージョンが古い場合動かさせない
if (($phpver = phpversion()) < "5.5.0") {
	die("PHP version 5.5.0 or higher is required for this program to work. <br>\n(Current PHP version:{$phpver})");
}
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONF_VER < 31 || !defined('CONF_VER')) {
	die("コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.");
}

//管理パスが初期値(kanripass)の場合は動作させない
if ($admin_pass === 'kanripass') {
	die("管理パスが初期設定値のままです！危険なので動かしません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.");
}

//BladeOne v3.52
include (__DIR__.'/blade/lib/BladeOne.php');
use eftec\bladeone\BladeOne;

$views = __DIR__.'/templates/'.THEMEDIR; // テンプレートフォルダ
$cache = __DIR__.'/cache'; // キャッシュフォルダ 
$blade = new BladeOne($views,$cache,BladeOne::MODE_AUTO); // MODE_DEBUGだと開発モード MODE_AUTOが速い。
$blade->pipeEnable = true; // パイプのフィルターを使えるようにする

$var_b = array(); // bladeに格納する変数

//var_dump($_POST);

//絶対パス取得
$path = realpath("./").'/'.IMG_DIR;
$temppath = realpath("./").'/'.TEMP_DIR;

$message = "";
$self = PHP_SELF;

$var_b += array('ver'=>ROIS_VER);
$var_b += array('base'=>BASE);
$var_b += array('btitle'=>TITLE);
$var_b += array('home'=>HOME);
$var_b += array('self'=>PHP_SELF);
$var_b += array('message'=>$message);
$var_b += array('pdefw'=>PDEF_W);
$var_b += array('pdefh'=>PDEF_H);
$var_b += array('pmaxw'=>PMAX_W);
$var_b += array('pmaxh'=>PMAX_H);
$var_b += array('themedir'=>THEMEDIR);
$var_b += array('tname'=>THEME_NAME);
$var_b += array('tver'=>THEME_VER);

$var_b += array('use_shi_p'=>USE_SHI_PAINTER);
$var_b += array('use_chicken'=>USE_CHICKENPAINT);

$var_b += array('dispid'=>DISP_ID);
$var_b += array('updatemark'=>UPDATE_MARK);
$var_b += array('use_resub'=>USE_RESUB);

$var_b += array('useanime'=>USE_ANIME);
$var_b += array('defanime'=>DEF_ANIME);
$var_b += array('use_continue'=>USE_CONTINUE);

$var_b += array('use_name'=>USE_NAME);
$var_b += array('use_com'=>USE_COM);
$var_b += array('use_sub'=>USE_SUB);

$var_b += array('addinfo'=>$addinfo);

$var_b += array('dptime'=>DSP_PAINTTIME);

$var_b += array('share_button'=>SHARE_BUTTON);

$path = realpath("./").'/'.IMG_DIR;
$temppath = realpath("./").'/'.TEMP_DIR;

//指定した日数を過ぎたスレッドのフォームを閉じる　→def()へ
if(!defined('ELAPSED_DAYS')){//config.phpで未定義なら0
	define('ELAPSED_DAYS','0');
}

$var_b += array('use_hashtag'=>USE_HASHTAG);

//ペイント画面の$pwdの暗号化
if(!defined('CRYPT_PASS')){//config.phpで未定義なら初期値が入る
	define('CRYPT_PASS','qRyFfhV6nyUggSb');//暗号鍵初期値
	}
define('CRYPT_METHOD','aes-128-cbc');
define('CRYPT_IV','T3pkYxNyjN7Wz3pu');//半角英数16文字

/* オートリンク */
function auto_link($proto){
	if(!(stripos($proto,"script")!==false)){//scriptがなければ続行
	$proto = preg_replace("{(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)}","<a href=\"\\1\\2\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">\\1\\2</a>",$proto);
	return $proto;
	}else{
	return $proto;
	}
}

/* ハッシュタグリンク */
function hashtag_link($hashtag) {
	$self = PHP_SELF;
	$hashtag = preg_replace("/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u", " <a href=\"{$self}?mode=search&amp;tag=tag&amp;search=\\1\">#\\1</a>", $hashtag);
	return $hashtag;
}

$mode = filter_input(INPUT_POST, 'mode');

//var_dump($_GET);
if(filter_input(INPUT_GET, 'mode')==="anime"){
	$pch = filter_input(INPUT_GET, 'pch');
	$mode = "anime";
}
if(filter_input(INPUT_GET, 'mode')==="continue"){
	$no = filter_input(INPUT_GET, 'no',FILTER_VALIDATE_INT);
	$mode = "continue";
}
if(filter_input(INPUT_GET, 'mode')==="admin"){
	$mode = "admin";
}
if(filter_input(INPUT_GET, 'mode')==="admin_in"){
	$mode = "admin_in";
}
if(filter_input(INPUT_GET, 'mode')==="piccom"){
	$stime = filter_input(INPUT_GET, 'stime',FILTER_VALIDATE_INT);
	$resto = filter_input(INPUT_GET, 'resto',FILTER_VALIDATE_INT);
	$mode = "piccom";
}
if(filter_input(INPUT_GET, 'mode')==="picrep"){
	$no = filter_input(INPUT_GET, 'no');
	$pwd = filter_input(INPUT_GET, 'pwd');
	$repcode = filter_input(INPUT_GET, 'repcode');
	$stime = filter_input(INPUT_GET, 'stime',FILTER_VALIDATE_INT);
	$mode = "picrep";
}
if(filter_input(INPUT_GET, 'mode')==="regist"){
	$mode = "regist";
}
if(filter_input(INPUT_GET, 'mode')==="res"){
	$mode = "res";
}
if(filter_input(INPUT_GET, 'mode')==="sodane"){
	$mode = "sodane";
	$resto = filter_input(INPUT_GET, 'resto',FILTER_VALIDATE_INT);
}
if(filter_input(INPUT_GET, 'mode')==="rsodane"){
	$mode = "rsodane";
	$resto = filter_input(INPUT_GET, 'resto',FILTER_VALIDATE_INT);
}
if(filter_input(INPUT_GET, 'mode')==="continue"){
	$no = filter_input(INPUT_GET, 'no');
	$mode = "continue";
}
if(filter_input(INPUT_GET, 'mode')==="del"){
	$mode = "del";
}
if(filter_input(INPUT_GET, 'mode')==="edit"){
	$mode = "edit";
}
if(filter_input(INPUT_GET, 'mode')==="editexec"){
	$mode = "editexec";
}
if(filter_input(INPUT_GET, 'mode')==="catalog"){
	$mode = "catalog";
}
if(filter_input(INPUT_GET, 'mode')==="search"){
	$mode = "search";
}

$message ="";
$sub = filter_input(INPUT_POST, 'sub');
$name = filter_input(INPUT_POST, 'name');
$mail = filter_input(INPUT_POST, 'mail');
$url = filter_input(INPUT_POST, 'url');
$com = filter_input(INPUT_POST, 'com');
$parent = trim(filter_input(INPUT_POST, 'parent'));
$picfile = trim(filter_input(INPUT_POST, 'picfile'));
$invz = trim(filter_input(INPUT_POST, 'invz'));
$img_w = trim(filter_input(INPUT_POST, 'img_w'));
$img_h = trim(filter_input(INPUT_POST, 'img_h'));
$time = trim(filter_input(INPUT_POST, 'time'));
$pwd = trim(filter_input(INPUT_POST, 'pwd'));
$pwdh = password_hash($pwd,PASSWORD_DEFAULT);
$exid = trim(filter_input(INPUT_POST, 'exid'));

//var_dump($_COOKIE);

$pwdc = filter_input(INPUT_COOKIE, 'pwdc');
$usercode = filter_input(INPUT_COOKIE, 'usercode');//nullならuser-codeを発行

//$_SERVERから変数を取得
//var_dump($_SERVER);

$req_method = ( isset($_SERVER["REQUEST_METHOD"]) === true ) ? ($_SERVER["REQUEST_METHOD"]): "";
//INPUT_SERVER が動作しないサーバがあるので$_SERVERを使う。

//ユーザーip
function get_uip(){
	if ($userip = getenv("HTTP_CLIENT_IP")) {
		return $userip;
	} elseif ($userip = getenv("HTTP_X_FORWARDED_FOR")) {
		return $userip;
	} elseif ($userip = getenv("REMOTE_ADDR")) {
		return $userip;
	} else {
		return $userip;
	}
}
//csrfトークンを作成
function get_csrf_token(){
	if(!isset($_SESSION)){
		session_start();
	}
	header('Expires:');
	header('Cache-Control:');
	header('Pragma:');
	return hash('sha256', session_id(), false);
}
//csrfトークンをチェック	
function check_csrf_token(){
	session_start();
	$token=filter_input(INPUT_POST,'token');
	$session_token=isset($_SESSION['token']) ? $_SESSION['token'] : '';
	if(!$session_token||$token!==$session_token){
		error(MSG006);
	}
}

//user-codeの発行
if(!$usercode){//falseなら発行
	$userip = get_uip();
	$usercode = substr(crypt(md5($userip.ID_SEED.date("Ymd", time())),'id'),-12);
	//念の為にエスケープ文字があればアルファベットに変換
	$usercode = strtr($usercode,"!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~","ABCDEFGHIJKLMNOabcdefghijklmn");
}
setcookie("usercode", $usercode, time()+(86400*365));//1年間

$var_b += array('usercode'=>$usercode);

/*-----------Main-------------*/

init();		//←初期設定後は不要なので削除可
deltemp();

/*-----------mode-------------*/

switch($mode){
	case 'regist':
		return regist();
	case 'res':
		return res();
	case 'sodane':
		return sodane();
	case 'rsodane':
		return rsodane();
	case 'paint':
		$palette = "";
		return paintform($palette);
	case 'piccom':
		return paintcom();
	case 'anime':
		if(!isset($sp)){$sp="";}
		return openpch($pch,$sp);
	case 'continue':
		return incontinue($no);
	case 'contpaint':
		//パスワードが必要なのは差し換えの時だけ
		if(CONTINUE_PASS||$type==='rep') usrchk();
		// if(ADMIN_NEWPOST) $admin=$pwd;
		$palette="";
		return paintform($palette);
	case 'picrep':
		return picreplace($no,$pwd);
	case 'catalog':
		return catalog();
	case 'search':
		return search();
	case 'edit':
		return editform();
	case 'editexec':
		return editexec();
	case 'del':
		return delmode();
	case 'admin_in':
		return admin_in();
	case 'admin':
		return admin();
	default:
		return def();
}
exit;

//投稿があればデータベースへ保存する
/* 記事書き込み */
function regist() {
	global $name,$com,$sub,$parent,$picfile,$img_w,$img_h,$mail,$url,$time,$pwd,$pwdh,$exid,$invz;
	global $badip;
	global $req_method;
	global $var_b,$blade;

	//CSRFトークンをチェック
	if(CHECK_CSRF_TOKEN){
		check_csrf_token();
	}

	if($req_method !== "POST") {error(MSG006);}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com,$name,$mail,$url,$sub);
	if(USE_NAME && !$name) {error(MSG009);}
	//レスの時は本文必須
	if(filter_input(INPUT_POST, 'modid') && !$com) {error(MSG008);}
	if(USE_COM && !$com) {error(MSG008);}
	if(USE_SUB && !$sub) {error(MSG010);}

	if(strlen($com) > MAX_COM) {error(MSG011);}
	if(strlen($name) > MAX_NAME) {error(MSG012);}
	if(strlen($mail) > MAX_EMAIL) {error(MSG013);}
	if(strlen($sub) > MAX_SUB) {error(MSG014);}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach($badip as $value){ //拒絶host
		if(preg_match("/$value$/i",$host)) {error(MSG016);}
	}
	//セキュリティ関連ここまで

	//$ptime='';
	//if($picfile){
	//	$path_filename=pathinfo($picfile, PATHINFO_FILENAME );//拡張子除去
	//	$fp = fopen(TEMP_DIR.$path_filename.".dat", "r");
	//	$userdata = fread($fp, 1024);
	//	fclose($fp);
	//	list($uip,$uhost,,,$ucode,,$starttime,$postedtime) = explode("\t", rtrim($userdata));
	//	//描画時間を$userdataをもとに計算
	//	if($starttime && DSP_PAINTTIME){
	//		$ptime = (int)$postedtime - (int)$starttime;
	//	}
	//	$time = (int)$ptime;
	//}

	//描画時間
	$pptime = (filter_input(INPUT_POST, 'pptime'));
	$time = (int)$pptime;
	
	try {
		$db = new PDO("sqlite:rois.db");
		if (isset($_POST["send"] ) ===  true) {

			$strlen_com=strlen($com);

			if ( $name   === "" ) $name = DEF_NAME;
			if ( $com  === "" ) $com  = DEF_COM;
			if ( $sub  === "" ) $sub  = DEF_SUB;

			$utime = time();
			if ($parent == 0 ) {
				$parent = $utime;
			}
			$tree = ($parent * 1000000000) - $utime;

			// 二重投稿チェック
			if (empty($_POST["modid"])==true) {
				// スレ立ての場合
				$table = 'tablelog';
				$wid = 'tid';
			} else {
				// レスの場合
				$table = 'tabletree';
				$wid = 'iid';
			}
			//最新コメント取得
			$sqlw = "SELECT sub, com, host, picfile FROM $table ORDER BY $wid DESC LIMIT 1";
			$msgw = $db->prepare($sqlw);
			$msgw->execute();
			$msgwc = $msgw->fetch();
			if(!empty($msgwc)){
				$msgsub = $msgwc["sub"]; //最新タイトル
				$msgwcom = $msgwc["com"]; //最新コメント取得できた
				$msgwhost = $msgwc["host"]; //最新ホスト取得できた
				//どれも一致すれば二重投稿だと思う
				if($strlen_com > 0 && $com == $msgwcom && $host == $msgwhost && $sub == $msgsub ){
					$msgs = null;
					$msgw = null;
					$db = null; //db切断
					error('二重投稿ですか？');
					exit;
				}
				//画像番号が一致の場合(投稿してブラウザバック、また投稿とか)
				//二重投稿と判別(画像がない場合は処理しない)
				if($msgwc["picfile"] !== "" && $picfile == $msgwc["picfile"]){
					error('二重投稿ですか？');
					exit;
				}
			}
			//↑二重投稿チェックおわり

			//画像ファイルとか処理
			if ( $picfile == true ) {
				list($img_w,$img_h)=getimagesize(TEMP_DIR.$picfile);
				rename( TEMP_DIR.$picfile , IMG_DIR.$picfile );
				chmod( IMG_DIR.$picfile , 0606);
				$path_filename=pathinfo($picfile, PATHINFO_FILENAME );//拡張子除去
				$picdat = $path_filename.'.dat';
				chmod( TEMP_DIR.$picdat, 0606 );
				unlink( TEMP_DIR.$picdat );

				$spchfile = $path_filename.'.spch';
				$pchfile = $path_filename.'.pch';
				
				if ( is_file(TEMP_DIR.$pchfile) == TRUE ) {
					rename( TEMP_DIR.$pchfile, IMG_DIR.$pchfile );
					chmod( IMG_DIR.$pchfile , 0606);
				} elseif( is_file(TEMP_DIR.$spchfile) == TRUE ) {
					rename( TEMP_DIR.$spchfile, IMG_DIR.$spchfile );
					chmod( IMG_DIR.$spchfile , 0606);
					$pchfile = $spchfile;
				} else {
					$pchfile = "";
				}
			} else {
				$img_w = 0;
				$img_h = 0;
				$pchfile = "";
			}

			// URLとメールにリンク
			if(AUTOLINK) $com = auto_link($com);
			//ハッシュタグ
			if(USE_HASHTAG) $com = hashtag_link($com);

			// '>'色設定
			$com = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1".RE_START."\\2".RE_END, $com);

			// 連続する空行を一行
			$com = preg_replace("/\n((　| )*\n){3,}/","\n",$com);

			//age_sageカウント 兼 レス数カウント
			$sql = "SELECT COUNT(*) as cnt FROM tabletree WHERE invz=0";
			$counts = $db->query("$sql");
			$count = $counts->fetch();
			$age = $count["cnt"];

			//スレッド数カウント
			$sql = "SELECT COUNT(*) as cnti FROM tablelog WHERE invz=0";
			$countsi = $db->query("$sql");
			$counti = $countsi->fetch();
			$logt = $counti["cnti"];

			// 値を追加する
			// スレ建ての場合
			if (empty($_POST["modid"])==true && $logt <= LOG_MAX_T) {
				//id生成
				$id = substr(crypt(md5($host.ID_SEED.date("Ymd", $utime)),'id'),-8);
				$sql = "INSERT INTO tablelog (created, modified, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, age, invz, host) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$age', '$invz', '$host')";
				$db = $db->exec($sql);
			} elseif(empty($_POST["modid"])==true && $logt > LOG_MAX_T) {
				//ログ行数オーバーの場合
				//id生成
				$id = substr(crypt(md5($host.ID_SEED.date("Ymd", $utime)),'id'),-8);
				$sql = "INSERT INTO tablelog (created, modified, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, age, invz, host) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$age', '$invz', '$host')";
				$db->exec($sql);
				//最初の行にある画像の名前を取得
				$sqlimg = "SELECT picfile FROM tablelog ORDER BY tid LIMIT 1";
				$msgs = $db->prepare($sqlimg);
				$msgs->execute();
				$msg = $msgs->fetch();
				$msgpic = $msg["picfile"]; //画像の名前取得できた
				//画像とかの削除処理
				if (is_file(IMG_DIR.$msgpic)) {
					$msgdat =pathinfo($msgpic, PATHINFO_FILENAME );//拡張子除去
					if (is_file(IMG_DIR.$msgdat.'.png')) {
						unlink(IMG_DIR.$msgdat.'.png');
					}
					if (is_file(IMG_DIR.$msgdat.'.jpg')) {
						unlink(IMG_DIR.$msgdat.'.jpg'); //一応jpgも
					}
					if (is_file(IMG_DIR.$msgdat.'.pch')) {
						unlink(IMG_DIR.$msgdat.'.pch'); 
					}
					if (is_file(IMG_DIR.$msgdat.'.spch')) {
						unlink(IMG_DIR.$msgdat.'.spch'); 
					}
					if (is_file(IMG_DIR.$msgdat.'.dat')) {
						unlink(IMG_DIR.$msgdat.'.dat'); 
					}
				}
				//↑画像とか削除処理完了
				//db最初の行を削除
				$sqldel = "DELETE FROM tablelog ORDER BY tid LIMIT 1";
				$db = $db->exec($sqldel);
			} elseif(empty($_POST["modid"])!=true && strpos($mail,'sage')!==false ) {
				//レスの場合でメール欄にsageが含まれる
				$tid = filter_input(INPUT_POST, 'modid');
				//id生成
				$id = substr(crypt(md5($host.ID_SEED.date("Ymd", $utime)),'id'),-8);
				if ($age <= LOG_MAX_R) {
					$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host')";
					$db = $db->exec($sql);
				} else {
					//ログ行数オーバーの場合
					$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host')";
					$db->exec($sql);
					//レス画像貼りは今のところ未対応だけど念のため
					//最初の行にある画像の名前を取得
					$sqlimg = "SELECT picfile FROM tabletree ORDER BY iid LIMIT 1";
					$msgs = $db->prepare($sqlimg);
					$msgs->execute();
					$msg = $msgs->fetch();
					$msgpic = $msg["picfile"]; //画像の名前取得できた
					//画像とかの削除処理
					if (is_file(IMG_DIR.$msgpic)) {
						$msgdat =pathinfo($msgpic, PATHINFO_FILENAME );//拡張子除去

						if (is_file(IMG_DIR.$msgdat.'.png')) {
						unlink(IMG_DIR.$msgdat.'.png');
						}
						if (is_file(IMG_DIR.$msgdat.'.jpg')) {
							unlink(IMG_DIR.$msgdat.'.jpg'); //一応jpgも
						}
						if (is_file(IMG_DIR.$msgdat.'.pch')) {
							unlink(IMG_DIR.$msgdat.'.pch'); 
						}
						if (is_file(IMG_DIR.$msgdat.'.spch')) {
							unlink(IMG_DIR.$msgdat.'.spch'); 
						}
						if (is_file(IMG_DIR.$msgdat.'.dat')) {
							unlink(IMG_DIR.$msgdat.'.dat'); 
						}
					}
					//↑画像とか削除処理完了
					//db最初の行を削除
					$sqlresdel = "DELETE FROM tabletree ORDER BY iid LIMIT 1";
					$db = $db->exec($sqlresdel);
				}
			} else {
				//レスの場合でメール欄にsageが含まれない
				$tid = filter_input(INPUT_POST, 'modid');
				//id生成
				$id = substr(crypt(md5($host.ID_SEED.date("Ymd", $utime)),'id'),-8);
				//age処理するかどうか
				//スレのレス数を数える
				$sqlr = "SELECT COUNT(*) as cntres FROM tabletree WHERE tid =  '$tid' AND invz=0";
				$countsr = $db->query("$sqlr");
				$countr = $countsr->fetch();
				$resn = $countr["cntres"]; //スレのレス数取得できた

				if ($age <= LOG_MAX_R) {
					if($resn < MAX_RES){ //レス数が指定値より少ないならage
						$nage = $age +1;
						$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host'); UPDATE tablelog set age = '$nage' where tid = '$tid'";
					} else {
						$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host')";
					}
					$db = $db->exec($sql);
				} else {
					//ログ行数オーバーの場合
					$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$time', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host')";
					$db->exec($sql);
					//レス画像貼りは今のところ未対応だけど念のため
					//最初の行にある画像の名前を取得
					$sqlimg = "SELECT picfile FROM tabletree ORDER BY iid LIMIT 1";
					$msgs = $db->prepare($sqlimg);
					$msgs->execute();
					$msg = $msgs->fetch();
					$msgpic = $msg["picfile"]; //画像の名前取得できた
					//画像とかの削除処理
					if (is_file(IMG_DIR.$msgpic)) {
						$msgdat = str_replace( strrchr($msgpic,"."), "", $msgpic); //拡張子除去
						if (is_file(IMG_DIR.$msgdat.'.png')) {
						unlink(IMG_DIR.$msgdat.'.png');
						}
						if (is_file(IMG_DIR.$msgdat.'.jpg')) {
							unlink(IMG_DIR.$msgdat.'.jpg'); //一応jpgも
						}
						if (is_file(IMG_DIR.$msgdat.'.pch')) {
							unlink(IMG_DIR.$msgdat.'.pch'); 
						}
						if (is_file(IMG_DIR.$msgdat.'.spch')) {
							unlink(IMG_DIR.$msgdat.'.spch'); 
						}
						if (is_file(IMG_DIR.$msgdat.'.dat')) {
							unlink(IMG_DIR.$msgdat.'.dat'); 
						}
					}
					//↑画像とか削除処理完了
					//db最初の行を削除
					$sqlresdel = "DELETE FROM tabletree ORDER BY iid LIMIT 1";
					$db = $db->exec($sqlresdel);
				}
			}

			$c_pass = $pwd;
			$names = $name;

			//-- クッキー保存 --
			//漢字を含まない項目はこちらの形式で追加
			setcookie ("pwdc", $c_pass,time()+(SAVE_COOKIE*24*3600));

			//クッキー項目："クッキー名<>クッキー値"　※漢字を含む項目はこちらに追加
			$cooks = array("namec<>".$names,"emailc<>".$mail,"urlc<>".$url);

			foreach ( $cooks as $cook ) {
				list($c_name,$c_cookie) = explode('<>',$cook);
				// $c_cookie = str_replace("&amp;", "&", $c_cookie);
				setcookie ($c_name, $c_cookie,time()+(SAVE_COOKIE*24*3600));
			}

			$var_b += array('message'=>'書き込みに成功しました。');
			$msgs = null;
			$msgw = null;
			$count = null;
			$counts = null;
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	unset($name,$mail,$sub,$com,$url,$pwd,$pwdh,$resto,$pictmp,$picfile,$mode);
	//header('Location:'.PHP_SELF);
	ok('書き込みに成功しました。画面を切り替えます。');
}

//通常表示モード
function def() {
	global $var_b,$blade;
	$dsp_res = DSP_RES;
	$page_def = PAGE_DEF;

	//古いスレのレスボタンを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$var_b += array('elapsed_time'=>$elapsed_time);
	$var_b += array('nowtime'=>$nowtime);

	//ページング
	try {
		$db = new PDO("sqlite:rois.db");
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
			$page = max($page,1);
		} else {
			$page = 1;
		}
		$start = $page_def * ($page - 1);

		//最大何ページあるのか
		$sql = "SELECT COUNT(*) as cnt FROM tablelog WHERE invz=0";
		$counts = $db->query("$sql");
		$count = $counts->fetch(); //スレ数取得できた
		$max_page = floor($count["cnt"] / $page_def) + 1;
		//最後にスレ数0のページができたら表示しない処理
		if(($count["cnt"] % $page_def) == 0){
			$max_page = $max_page - 1;
			//ただしそれが1ページ目なら困るから表示
			$max_page = max($max_page,1);
		}
		$var_b += array('max_page'=>$max_page);

		//リンク作成用
		$var_b += array('nowpage'=>$page);
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$var_b += array('paging'=>$paging);
		$var_b += array('pp'=>$pp);

		$var_b += array('back'=>($page - 1));

		$var_b += array('next'=>($page + 1));

		//そろそろ消える用
		//一番大きい（新しい）スレのIDを取得
		$sql_log_m = "SELECT tid FROM tablelog ORDER by tid DESC LIMIT 1";
		$log_mid = $db->prepare($sql_log_m);
		$log_mid->execute();
		$mid = $log_mid->fetch(); //取り出せた
		if(!empty($mid)) {
			$m_tid = $mid['tid'];
		} else {
			$m_tid = 0;
		} //一番大きいスレID または0
		$var_b += array('m_tid'=>$m_tid); //テーマのほうでこれから親idを引く
		// →「スレの古さ番号」が出る。大きいほど古い。
		//閾値を考える
		$thid = LOG_MAX_T * LOG_LIMIT/100; //閾値
		$var_b += array('thid'=>$thid);
		//テーマのほうでこの数字と「スレの古さ番号」を比べる
		//thidよりスレの古さ番号が大きいスレは消えるリミットフラグが立つ

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//読み込み
	
	try {
		$db = new PDO("sqlite:rois.db");
		//1ページの全スレッド取得
		$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE invz=0 ORDER BY age DESC, tree DESC LIMIT $start,$page_def"; 
		$posts = $db->query($sql);

		$ko = array();
		$oya = array();

		$i = 0;
		$j = 0;
		while ( $i < PAGE_DEF) {
			$bbsline = $posts->fetch();
			if(empty($bbsline)){break;} //スレがなくなったら抜ける
			$oid = $bbsline["tid"]; //スレのtid(親番号)を取得
			$sqli = "SELECT iid, tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent FROM tabletree WHERE tid = $oid and invz=0 ORDER BY tree DESC";
			//レス取得
			$postsi = $db->query($sqli);
			$j = 0;
			$flag = true;
			while ( $flag == true) {
				$bbsline['time']=is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
				$res = $postsi->fetch();
				if(empty($res)){ //レスがなくなったら
					$bbsline['ressu'] = $j; //スレのレス数
					$bbsline['res_d_su'] = $j - DSP_RES; //スレのレス省略数
					if ($j > DSP_RES) { //スレのレス数が規定より多いと
						$bbsline['rflag'] = true; //省略フラグtrue
					} else {
						$bbsline['rflag'] = false; //省略フラグfalse
					}
					$flag = false;
					break;
				} //抜ける
				$res['resno'] = ($j +1); //レス番号
				// http、https以外のURLの場合表示しない
				if(!filter_var($res['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['url'])) {
					$res['url'] = "";
				}
				$res['com'] = nl2br(htmlentities($res['com'],ENT_QUOTES | ENT_HTML5), false);
				$ko[] = $res;
				$j++;
			}
			// http、https以外のURLの場合表示しない
			if(!filter_var($bbsline['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['url'])) {
				$bbsline['url'] = "";
			}
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$i++;
		}

		$var_b += array('ko'=>$ko);
		$var_b += array('oya'=>$oya);
		$var_b += array('dsp_res'=>DSP_RES);
		$var_b += array('path'=>IMG_DIR);

		echo $blade->run(MAINFILE,$var_b);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//カタログモード
function catalog() {
	global $blade,$var_b;
	$page_def = CATALOG_N;

	//ページング
	try {
		$db = new PDO("sqlite:rois.db");
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
			$page = max($page,1);
		} else {
			$page = 1;
		}
		$start = $page_def * ($page - 1);

		//最大何ページあるのか
		$sql = "SELECT COUNT(*) as cnt FROM tablelog WHERE invz=0";
		$counts = $db->query("$sql");
		$count = $counts->fetch(); //スレ数取得できた
		$max_page = floor($count["cnt"] / $page_def) + 1;
		//最後にスレ数0のページができたら表示しない処理
		if(($count["cnt"] % $page_def) == 0){
			$max_page = $max_page - 1;
			//ただしそれが1ページ目なら困るから表示
			$max_page = max($max_page,1);
		}
		$var_b += array('max_page'=>$max_page);

		//リンク作成用
		$var_b += array('nowpage'=>$page);
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$var_b += array('paging'=>$paging);
		$var_b += array('pp'=>$pp);

		$var_b += array('back'=>($page - 1));

		$var_b += array('next'=>($page + 1));

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//読み込み
	
	try {
		$db = new PDO("sqlite:rois.db");
		//1ページの全スレッド取得
		$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE invz=0 ORDER BY age DESC, tree DESC LIMIT $start,$page_def"; 
		$posts = $db->query($sql);

		$oya = array();

		$i = 0;
		while ( $i < CATALOG_N) {
			$bbsline = $posts->fetch();
			if(empty($bbsline)){break;} //スレがなくなったら抜ける
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$i++;
		}

		$var_b += array('oya'=>$oya);
		$var_b += array('path'=>IMG_DIR);

		//$smarty->debugging = true;
		$var_b += array('catalogmode'=>'catalog');
		echo $blade->run(CATALOGFILE,$var_b);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//検索モード 現在全件表示のみ対応
function search() {
	global $blade,$var_b;

	$search = filter_input(INPUT_GET, 'search');
	//部分一致検索
	$bubun =  filter_input(INPUT_GET, 'bubun');
	//本文検索
	$tag = filter_input(INPUT_GET, 'tag');

	//読み込み
	try {
		$db = new PDO("sqlite:rois.db");
		//全スレッド取得
		//まずtagがあれば本文検索
		if ($tag === 'tag') {
			$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE com LIKE '%$search%' AND invz=0 ORDER BY age DESC, tree DESC";
			//レスも
			$sqli = "SELECT iid, tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent FROM tabletree WHERE com LIKE '%$search%' and invz=0 ORDER BY tree DESC";
			$var_b += array('catalogmode'=>'hashsearch');
			$var_b += array('tag'=>$search);
		} else {
			//tagがなければ作者名検索
			if($bubun === "bubun"){
				$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE name LIKE '%$search%' AND invz=0 ORDER BY age DESC, tree DESC"; 
			} else {
				$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE name LIKE '$search' AND invz=0 ORDER BY age DESC, tree DESC"; 
			}
			$var_b += array('catalogmode'=>'search');
			$var_b += array('author'=>$search);
		}
		
		$posts = $db->query($sql);

		$oya = array();

		$i = 0;
		while ($bbsline = $posts->fetch()) {
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$i++;
		}
		//tagがあればレスも検索
		if ($tag === 'tag') {
			$ko = array();
			$postsi = $db->query($sqli);
			while ($res = $postsi->fetch()) {
				$ko[] = $res;
				$i++;
			}
			$var_b += array('ko'=>$ko);
		}

		$var_b += array('oya'=>$oya);
		$var_b += array('path'=>IMG_DIR);

		//$smarty->debugging = true;
		$var_b += array('s_result'=>$i);
		echo $blade->run(CATALOGFILE,$var_b);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//そうだね
function sodane(){
	$resto = filter_input(INPUT_GET, 'resto');
	try {
		$db = new PDO("sqlite:rois.db");
		$sql = "UPDATE tablelog set exid = exid+1 where tid = '$resto'";
		$db = $db->exec($sql);
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	header('Location:'.PHP_SELF);
	def();
}

//レスそうだね
function rsodane(){
	$resto = filter_input(INPUT_GET, 'resto');
	try {
		$db = new PDO("sqlite:rois.db");
		$sql = "UPDATE tabletree set exid = exid+1 where iid = '$resto'";
		$db = $db->exec($sql);
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	header('Location:'.PHP_SELF);
	def();
}

//レス画面

function res(){
	global $blade,$var_b;
	$resno = filter_input(INPUT_GET, 'res');
	$var_b += array('resno'=>$resno);

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$var_b += array('token'=>$token);
	}

	//古いスレのレスフォームを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$var_b += array('elapsed_time'=>$elapsed_time);
	$var_b += array('nowtime'=>$nowtime);

	try {
		$db = new PDO("sqlite:rois.db");
		$sql = "SELECT * FROM tablelog WHERE tid = $resno ORDER BY tree DESC";
		$posts = $db->query($sql);
	
		$oya = array();
		$ko = array();
		while ($bbsline = $posts->fetch() ) {
			$bbsline['time']=is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
			//スレッドの記事を取得
			$sqli = "SELECT * FROM tabletree WHERE (invz = 0 AND tid = $resno ) ORDER BY tree DESC";
			$postsi = $db->query($sqli);
			$rresname = array();
			while ($res = $postsi->fetch()){
				$res['com'] = nl2br(htmlentities($res['com'],ENT_QUOTES | ENT_HTML5), false);
				$ko[] = $res;
				//投稿者名取得
				if (!in_array($res['name'], $rresname)) {//重複除外
					$rresname[] = $res['name'];//投稿者名を配列に入れる
				}
				// http、https以外のURLの場合表示しない
				if(!filter_var($res['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['url'])) {
					$res['url'] = "";
				}
			}
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			if (!in_array($bbsline['name'], $rresname)) {
				$rresname[] = $bbsline['name'];
			}
			// http、https以外のURLの場合表示しない
			if(!filter_var($bbsline['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['url'])) {
				$bbsline['url'] = "";
			}
			$resname = implode('さん ',$rresname);
			$var_b += array('resname'=>$resname);

			$var_b += array('oya'=>$oya);
			$var_b += array('ko'=>$ko);
		}
		//そろそろ消える用
		//一番大きい（新しい）スレのIDを取得
		$sql_log_m = "SELECT tid FROM tablelog ORDER by tid DESC LIMIT 1";
		$log_mid = $db->prepare($sql_log_m);
		$log_mid->execute();
		$mid = $log_mid->fetch(); //取り出せた
		if(!empty($mid)) {
			$m_tid = $mid['tid'];
		} else {
			$m_tid = 0;
		} //一番大きいスレID または0
		$var_b += array('m_tid'=>$m_tid); //テーマのほうでこれから親idを引く
		// →「スレの古さ番号」が出る。大きいほど古い。
		//閾値を考える
		$thid = LOG_MAX_T * LOG_LIMIT/100; //閾値
		$var_b += array('thid'=>$thid);
		//テーマのほうでこの数字と「スレの古さ番号」を比べる
		//thidよりスレの古さ番号が大きいスレは消えるリミットフラグが立つ
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	
	$var_b += array('path'=>IMG_DIR);

	echo $blade->run(RESFILE,$var_b);
}

//お絵描き画面
function paintform(){
	global $message,$usercode,$quality,$qualitys,$pwd,$no;
	global $mode,$ctype,$pch,$type;
	global $blade,$var_b;
	global $pallets_dat;

	//ツール
	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$var_b += array('tool'=>$tool);

	$var_b += array('mode'=>'piccom');
	$var_b += array('btitle'=>TITLE);
	$var_b += array('home'=>HOME);
	$var_b += array('self'=>PHP_SELF);
	$var_b += array('message'=>$message);
	$var_b += array('pdefw'=>PDEF_W);
	$var_b += array('pdefh'=>PDEF_H);

	$picw = filter_input(INPUT_POST, 'picw',FILTER_VALIDATE_INT);
	$pich = filter_input(INPUT_POST, 'pich',FILTER_VALIDATE_INT);
	$anime = isset($_POST["anime"]) ? true : false;
	$var_b += array('anime'=>$anime);
	
	if($picw < 300) $picw = 300;
	if($pich < 300) $pich = 300;
	if($picw > PMAX_W) $picw = PMAX_W;
	if($pich > PMAX_H) $pich = PMAX_H;

	$var_b += array('picw'=>$picw);
	$var_b += array('pich'=>$pich);

	if($tool == "shi") { //しぃペインターの時の幅と高さ
		$ww = $picw + 510;
		$hh = $pich + 172;
	} else { //NEOのときの幅と高さ
		$ww = $picw + 150;
		$hh = $pich + 172;
	}
	if($hh < 560){$hh = 560;}//共通の最低高
	$var_b += array('w'=>$ww);
	$var_b += array('h'=>$hh);
	
	$var_b += array('undo'=>UNDO);
	$var_b += array('undo_in_mg'=>UNDO_IN_MG);

	$var_b += array('useanime'=>USE_ANIME);

	$var_b += array('path'=>IMG_DIR);

	$var_b += array('stime'=>time());
	
	$userip = get_uip();

	//しぃペインター
	$var_b += array('layer_count'=>LAYER_COUNT);
	$qq = $quality ? $quality : $qualitys[0];
	$var_b += array('quality'=>$qq);

	if($mode=="contpaint"){
		$ctype = filter_input(INPUT_POST, 'ctype');
		$type = filter_input(INPUT_POST, 'type');
		$pwdf = filter_input(INPUT_POST, 'pwd');
		$var_b += array('no'=>$no);
		$var_b += array('pwd'=>$pwdf);
		$var_b += array('ctype',$ctype);
		if(is_file(IMG_DIR.$pch.'.pch')){
			$useneo = true;
			$var_b += array('useneo'=>true);
		}elseif(is_file(IMG_DIR.$pch.'.spch')){
			$useneo = false;
			$var_b += array('useneo'=>false);
		}
		if((C_SECURITY_CLICK || C_SECURITY_TIMER) && SECURITY_URL){
			$var_b += array('security'=>true);
			$var_b += array('security_click'=>C_SECURITY_CLICK);
			$var_b += array('security_timer'=>C_SECURITY_TIMER);
		}
	}else{
		if((SECURITY_CLICK || SECURITY_TIMER) && SECURITY_URL){
			$var_b += array('security'=>true);
			$var_b += array('security_click'=>SECURITY_CLICK);
			$var_b += array('security_timer'=>SECURITY_TIMER);
		}
		$var_b += array('newpaint'=>true);
	}
	$var_b += array('security_url'=>SECURITY_URL);

	//パレット設定
	$initial_palette = 'Palettes[0] = "#000000\n#FFFFFF\n#B47575\n#888888\n#FA9696\n#C096C0\n#FFB6FF\n#8080FF\n#25C7C9\n#E7E58D\n#E7962D\n#99CB7B\n#FCECE2\n#F9DDCF";';
	if(USE_SELECT_PALETTES){ //パレット切り替え機能を使う時
		foreach($pallets_dat as $i=>$value){
			if($i==filter_input(INPUT_POST, 'selected_palette_no',FILTER_VALIDATE_INT)){//キーと入力された数字が同じなら
				setcookie("palettec", $i, time()+(86400*SAVE_COOKIE));//Cookie保存
				if(is_array($value)){
					list($p_name,$p_dat)=$value;
					$lines=file($p_dat);
				}else{
					$lines=file($value);
				}
				break;
			}
		}
	}else{
		$lines=file(PALETTEFILE);//初期パレット
	}

	$pal=array();
	$DynP=array();

	$pal=array();
	$DynP=array();
	foreach ( $lines as $i => $line ) {
		$line=charconvert(str_replace(["\r","\n","\t"],"",$line));
		list($pid,$pname,$pal[0],$pal[2],$pal[4],$pal[6],$pal[8],$pal[10],$pal[1],$pal[3],$pal[5],$pal[7],$pal[9],$pal[11],$pal[12],$pal[13]) = explode(",", $line);
		$DynP[]=$pname;
		$p_cnt=$i+1;
		$palettes = 'Palettes['.$p_cnt.'] = "#';
		ksort($pal);
		$palettes.=implode('\n#',$pal);
		$palettes.='";';
		$arr_pal[$i] = $palettes;
	}
	$var_b += array('palettes'=>$initial_palette.implode('',$arr_pal));

	$var_b += array('palsize'=>(count($DynP) + 1));

	foreach ($DynP as $p){
		$arr_dynp[] = '<option>'.$p.'</option>';
	}
	$var_b += array('dynp'=>implode('',$arr_dynp));

	if($ctype=='pch'){
		$pchfile = filter_input(INPUT_POST, 'pch');
		$var_b += array('pchfile'=>IMG_DIR.$pchfile);
	}
	if($ctype=='img'){
		$var_b += array('animeform'=>false);
		$var_b += array('anime'=>false);
		$imgfile = filter_input(INPUT_POST, 'img');
		$var_b += array('imgfile'=>IMG_DIR.$imgfile);
	}
	$usercode.='&amp;stime='.time();//拡張ヘッダに描画開始時間をセット
	//差し換え時の認識コード追加
	if($type==='rep'){
		$no = filter_input(INPUT_POST, 'no',FILTER_VALIDATE_INT);
		$pwdf = filter_input(INPUT_POST, 'pwd');
		$time=time();
		$repcode = substr(crypt(md5($no.$userip.$pwdf.date("Ymd", $time)),$time),-8);
		//念の為にエスケープ文字があればアルファベットに変換
		$repcode = strtr($repcode,"!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~","ABCDEFGHIJKLMNOabcdefghijklmn");
		//パスワード暗号化
		$pwdf = openssl_encrypt ($pwdf,CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV);//暗号化
		$pwdf = bin2hex($pwdf);//16進数に
		$datmode = 'picrep&amp;no='.$no.'&amp;pwd='.$pwdf.'&amp;repcode='.$repcode;
		$var_b += array('mode'=>$datmode);
		$usercode.='&amp;repcode='.$repcode;
	}
	$var_b += array('usercode'=>$usercode); //usercodeにいろいろくっついたものをまとめて出力

	//出力
	echo $blade->run(PAINTFILE,$var_b);
}

//アニメ再生

function openpch($pch,$sp="") {
	global $blade,$var_b;
	$message = "";

	$pch = filter_input(INPUT_GET, 'pch');
	$pchh = str_replace( strrchr($pch,"."), "", $pch); //拡張子除去
	$extn = substr($pch, strrpos($pch, '.') + 1); //拡張子取得

	$picfile = IMG_DIR.$pchh.".png";

	if($extn=='spch'){
		$pchfile = IMG_DIR.$pch;
		$var_b += array('useneo'=>false); //拡張子がspchのときはしぃぺ
	}elseif($extn=='pch'){
		$pchfile = IMG_DIR.$pch;
		$var_b += array('useneo'=>true); //拡張子がpchのときはNEO
	}else { //動画が無い時は処理しない
		$w=$h=$picw=$pich=$datasize="";
		$var_b += array('useneo'=>true);
	}
	$datasize = filesize($pchfile);
	$size = getimagesize($picfile);
	if(!$sp) $sp = PCH_SPEED;
	$picw = $size[0];
	$pich = $size[1];
	$w = $picw;
	$h = $pich + 26;
	if($w < 300){$w = 300;}
	if($h < 326){$h = 326;}

	//$var_b += array('btitle'=>TITLE);
	//$var_b += array('home'=>HOME);
	//$var_b += array('self'=>PHP_SELF);
	//$var_b += array('message'=>$message);
	//$var_b += array('pdefw'=>PDEF_W);
	//$var_b += array('pdefh'=>PDEF_H);
	//$var_b += array('themedir'=>THEMEDIR);
	//$var_b += array('tver'=>TEMPLATE_VER);

	$var_b += array('picw'=>$picw);
	$var_b += array('pich'=>$pich);
	$var_b += array('w'=>$w);
	$var_b += array('h'=>$h);
	$var_b += array('pchfile'=>'./'.$pch);
	$var_b += array('datasize'=>$datasize);

	$var_b += array('speed'=>PCH_SPEED);

	$var_b += array('path'=>IMG_DIR);
	$var_b += array('a_stime'=>time());

	echo $blade->run(ANIMEFILE,$var_b);
}

//お絵かき投稿
function paintcom(){
	global $usercode,$stime,$ptime;
	global $blade,$var_b;

	$var_b += array('parent'=>$_SERVER['REQUEST_TIME']);
	$var_b += array('stime'=>$stime);
	$var_b += array('usercode'=>$usercode);

	//----------

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$var_b += array('token'=>$token);
	}

	//描画時間(表示用)
	if($stime){
		$ptime = calcPtime(time()-$stime);
	}
	$var_b += array('ptime'=>$ptime);
	//描画時間(内部用)
	if($stime){
		$pptime = time()-$stime;
	}
	$var_b += array('pptime'=>$pptime);

	//----------

	//var_dump($_POST);
	$userip = get_uip();
	//テンポラリ画像リスト作成
	$tmplist = array();
	$handle = opendir(TEMP_DIR);
	while ($file = readdir($handle)) {
		if(!is_dir($file) && preg_match("/\.(dat)$/i",$file)) {
			$fp = fopen(TEMP_DIR.$file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip,$uhost,$uagent,$imgext,$ucode,) = explode("\t", rtrim($userdata));
			$file_name = preg_replace("/\.(dat)$/i","",$file);
			if(is_file(TEMP_DIR.$file_name.$imgext)) //画像があればリストに追加
				$tmplist[] = $ucode."\t".$uip."\t".$file_name.$imgext;
		}
	}
	closedir($handle);
	$tmp = array();
	if(count($tmplist)!=0){
		//user-codeとipアドレスでチェック
		foreach($tmplist as $tmpimg){
			list($ucode,$uip,$ufilename) = explode("\t", $tmpimg);
			if($ucode == $usercode||$uip == $userip){
				$tmp[] = $ufilename;
			}
		}
	}

	$post_mode = true;
	$regist = true;
	$ipcheck = true;
	if(count($tmp)==0){
		$notmp = true;
		$pictmp = 1;
	}else{
		$pictmp = 2;
		sort($tmp);
		reset($tmp);
		$temp = array();
		foreach($tmp as $tmpfile){
			$src = TEMP_DIR.$tmpfile;
			$srcname = $tmpfile;
			$date = gmdate("Y/m/d H:i", filemtime($src)+9*60*60);
			$temp[] = compact('src','srcname','date');
		}
		$var_b += array('temp'=>$temp);
	}

	$tmp2 = array();
	$var_b += array('tmp'=>$tmp2);

	$var_b += array('path'=>IMG_DIR);

	echo $blade->run(PICFILE,$var_b);
}

//コンティニュー画面in レス画像には非対応
function incontinue($no) {
	global $blade,$var_b;
	$var_b += array('othermode'=>'incontinue');
	$var_b += array('continue_mode'=>true);
	$var_b += array('path'=>IMG_DIR);

	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$var_b += array('tool'=>$tool);

	//コンティニュー時は削除キーを常に表示
	$var_b += array('passflag'=>true);
	//新規投稿で削除キー不要の時 true
	if(! CONTINUE_PASS) {
		$var_b += array('newpost_nopassword'=>true);
	} else {
		$var_b += array('newpost_nopassword'=>false);
	}

	try{
		$db = new PDO("sqlite:rois.db");
		$sql = "SELECT * FROM tablelog WHERE picfile='$no' ORDER BY tree DESC";
		$posts = $db->query($sql);

		$oya = array();
		while ($bbsline = $posts->fetch() ) {
			$bbsline['time']=is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$var_b += array('oya'=>$oya); //配列に格納
		}
		$pchh = str_replace( strrchr($no,"."), "", $no); //拡張子除去
		$pchfilename = IMG_DIR.$pchh;
		if(is_file($pchfilename.'.spch')){
			//$pchfile = IMG_DIR.$pch;
			$var_b += array('tool'=>$shi);
			$var_b += array('useshi'=>true);
			$var_b += array('useneo'=>false); //拡張子がspchのときはしぃぺ
			$var_b += array('ctype_pch'=>true);
		}elseif(is_file($pchfilename.'.pch')){
			//$pchfile = IMG_DIR.$pch;
			$var_b += array('tool'=>$neo);
			$var_b += array('useshi'=>false);
			$var_b += array('useneo'=>true); //拡張子がpchのときはNEO
			$var_b += array('ctype_pch'=>true);
		}else { //どっちもない＝動画が無い時
			//$w=$h=$picw=$pich=$datasize="";
			$var_b += array('useneo'=>true);
			$var_b += array('useshi'=>true);
			$var_b += array('ctype_pch'=>false);
		}
		$var_b += array('ctype_img'=>true);

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}

	echo $blade->run(OTHERFILE,$var_b);
}

//削除くん

function delmode(){
	global $admin_pass;
	global $blade,$var_b;
	$delno = filter_input(INPUT_POST, 'delno');
	$delt = filter_input(INPUT_POST, 'delt'); //0親1レス削除

	$ppwd = filter_input(INPUT_POST, 'pwd');

	if ($delt == 0) {
		$deltable = 'tablelog';
		$idk = "tid";
	} else {
		$deltable = 'tabletree';
		$idk = "iid";
	}
	//記事呼び出し
	try {
		$db = new PDO("sqlite:rois.db");

		//パスワードを取り出す
		$sql ="SELECT pwd FROM $deltable WHERE $idk = $delno";
		$msgs = $db->prepare($sql);
		if ($msgs == false) {
			error('そんな記事ない気がします。');
		}
		$msgs->execute();
		$msg = $msgs->fetch();
		if (empty($msg)) {
			error('そんな記事ない気がします。');
		}

		//削除記事の画像を取り出す
		$sqlp ="SELECT picfile FROM $deltable WHERE $idk = $delno";
		$msgsp = $db->prepare($sqlp);
		$msgsp->execute();
		$msgp = $msgsp->fetch();
		if (empty($msgp)) {
			error('画像が見当たりません。');
		}
		$msgpic = $msgp['picfile']; //画像の名前取得できた

		if (isset($_POST["admindel"]) == true) {
			$admindelmode = 1;
		} else {
			$admindelmode = 0;
		}

		if (password_verify($ppwd,$msg['pwd']) === true) {
			//画像とかファイル削除
			if (is_file(IMG_DIR.$msgpic)) {
				$msgdat = str_replace( strrchr($msgpic,"."), "", $msgpic); //拡張子除去
				if (is_file(IMG_DIR.$msgdat.'.png')) {
					unlink(IMG_DIR.$msgdat.'.png');
				}
				if (is_file(IMG_DIR.$msgdat.'.jpg')) {
					unlink(IMG_DIR.$msgdat.'.jpg'); //一応jpgも
				}
				if (is_file(IMG_DIR.$msgdat.'.pch')) {
					unlink(IMG_DIR.$msgdat.'.pch'); 
				}
				if (is_file(IMG_DIR.$msgdat.'.spch')) {
					unlink(IMG_DIR.$msgdat.'.spch'); 
				}
				if (is_file(IMG_DIR.$msgdat.'.dat')) {
					unlink(IMG_DIR.$msgdat.'.dat'); 
				}
			}
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM $deltable WHERE $idk=$delno";
			$db = $db->exec($sql);
			$var_b += array('message'=>'削除しました。');
		} elseif ($admin_pass == $ppwd && $admindelmode == 1) {
			//画像とかファイル削除
			if (is_file(IMG_DIR.$msgpic)) {
				$msgdat = str_replace( strrchr($msgpic,"."), "", $msgpic); //拡張子除去
				if (is_file(IMG_DIR.$msgdat.'.png')) {
					unlink(IMG_DIR.$msgdat.'.png');
				}
				if (is_file(IMG_DIR.$msgdat.'.jpg')) {
					unlink(IMG_DIR.$msgdat.'.jpg'); //一応jpgも
				}
				if (is_file(IMG_DIR.$msgdat.'.pch')) {
					unlink(IMG_DIR.$msgdat.'.pch'); 
				}
				if (is_file(IMG_DIR.$msgdat.'.spch')) {
					unlink(IMG_DIR.$msgdat.'.spch'); 
				}
				if (is_file(IMG_DIR.$msgdat.'.dat')) {
					unlink(IMG_DIR.$msgdat.'.dat'); 
				}
			}
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM $deltable WHERE $idk=$delno";
			$db = $db->exec($sql);
			$var_b += array('message'=>'削除しました。');
		} elseif ($admin_pass == $ppwd && $admindelmode != 1) {
			//管理モード以外での管理者削除は
			//データベースから削除はせずに非表示
			$sql = "UPDATE $deltable SET invz=1 WHERE $idk=$delno";
			$db = $db->exec($sql);
			$var_b += array('message'=>'削除しました。');
		} else {
			error('パスワードまたは記事番号が違います。');
		}
		$db = null; 
		$msgp = null;
		$msg = null;//db切断 
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//変数クリア
	unset($delno,$delt);
	//header('Location:'.PHP_SELF);
	ok('削除しました。画面を切り替えます。');
}

//画像差し替え レス画像には非対応
function picreplace($no,$pwdf){
	global $path,$badip;
	$repcode = filter_input(INPUT_GET, 'repcode');
	$pwdf = filter_input(INPUT_GET, 'pwd');
	$pwdf = hex2bin($pwdf);//バイナリに
	$pwdf = openssl_decrypt($pwdf,CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV);//復号化
	
	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach($badip as $value){ //拒絶host
		if(preg_match("/$value$/i",$host)) error(MSG016);
	}

	/*--- テンポラリ捜査 ---*/
	$find=false;
	$handle = opendir(TEMP_DIR);
	while ($file = readdir($handle)) {
		if(!is_dir($file) && preg_match("/\.(dat)$/i",$file)) {
			$fp = fopen(TEMP_DIR.$file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip,$uhost,$uagent,$imgext,$ucode,$urepcode,$starttime,$postedtime) = explode("\t", rtrim($userdata)."\t");//区切りの"\t"を行末に190610
			$file_name = pathinfo($file, PATHINFO_FILENAME );//拡張子除去
			//画像があり、認識コードがhitすれば抜ける 
			if($file_name && is_file(TEMP_DIR.$file_name.$imgext) && $urepcode === $repcode){
				$find=true;
				break;
			}
		}
	}
	closedir($handle);
	if(!$find){
		error2();
		
	}

	//描画時間
	$psec='';
	$_ptime = '';
	if($psec=$postedtime-$starttime){
		$_ptime = calcPtime($psec);
	}


	// ログ読み込み
	try {
		$db = new PDO("sqlite:rois.db");
		//記事を取り出す
		$sql ="SELECT pwd, picfile, pchfile, time FROM tablelog WHERE tid = '$no'";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg_d = $msgs->fetch();

		//パスワード照合
		if(password_verify($pwdf,$msg_d["pwd"])||$msg_d["pwd"]=== substr(md5($pwdf),2,8)){
			//あってたら画像アップロード処理
			$picfile = $file_name.$imgext;

			if ( $picfile == true ) {
				rename( TEMP_DIR.$picfile , IMG_DIR.$picfile );
				chmod( IMG_DIR.$picfile , 0606);
				// $picdat = strtr($picfile , 'png', 'dat');
				$picdat = $file_name.'.dat';
				chmod( TEMP_DIR.$picdat , 0606);
				unlink( TEMP_DIR.$picdat );

				$pchfile = $file_name.'.pch';
				$spchfile = $file_name.'.spch';
				
				if ( is_file(TEMP_DIR.$pchfile) == TRUE ) {
					rename( TEMP_DIR.$pchfile, IMG_DIR.$pchfile );
					chmod( IMG_DIR.$pchfile , 0606);
				} elseif( is_file(TEMP_DIR.$spchfile) == TRUE ) {
					rename( TEMP_DIR.$spchfile, IMG_DIR.$spchfile );
					chmod( IMG_DIR.$spchfile , 0606);
					$pchfile = $spchfile;
				} else {
					$pchfile = "";
				}
			} else { //念のため
				$pchfile = "";
			}
			
			if(is_file($path.$msg_d["picfile"])) unlink($path.$msg_d["picfile"]);
			//旧ファイル削除
			if(is_file($path.$msg_d["picfile"])) unlink($path.$msg_d["picfile"]);
			if(is_file($path.$msg_d["pchfile"])) unlink($path.$msg_d["pchfile"]);
			// $msgedat = str_replace( strrchr($msg_d["picfile"],"."), "", $msg_d["picfile"]); //拡張子除去
			$msgedat = pathinfo($msg_d["picfile"], PATHINFO_FILENAME );//拡張子除去
			$msgedat = $msgedat.'.dat';
			if(is_file($path.$msgedat)) unlink($path.$msgedat);
			//描画時間追加
			if($msg_d["time"] && $_ptime) {
				$time = is_numeric($msg_d["time"]) ? ($msg_d["time"]+$psec) : $msg_d["time"].'+'.$_ptime;
			}
			//id生成
			$utime = time();
			$id = substr(crypt(md5($host.ID_SEED.date("Ymd", $utime)),'id'),-8);
			//db上書き
			$sqlrep = "UPDATE tablelog set modified = datetime('now', 'localtime'), picfile = '$picfile', pchfile = '$pchfile', host = '$host', id = '$id', time = '$time' where tid = $no";
			$db = $db->exec($sqlrep);
		} else {
			error(MSG028);
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	ok('編集に成功しました。画面を切り替えます。');
}


//編集モードくん入口
function editform() {
	global $admin_pass;
	global $blade,$var_b;

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$var_b += array('token'=>$token);
	}

	$editno = filter_input(INPUT_POST, 'delno');
	if ($editno == "") {
		error('記事番号を入力してください');
	}
	$editt = filter_input(INPUT_POST, 'delt'); //0親1レス
	if ($editt == 0) {
		$edittable = 'tablelog';
		$idk = "tid";
	} else {
		$edittable = 'tabletree';
		$idk = "iid";
		$var_b += array('resedit'=>'resedit');
	}
	//記事呼び出し
	try {
		$db = new PDO("sqlite:rois.db");

		//パスワードを取り出す
		$sql ="SELECT pwd FROM $edittable WHERE $idk = $editno";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg = $msgs->fetch();
		if (empty($msg)) {
			error('そんな記事ないです。');
		}
		$postpwd = filter_input(INPUT_POST, 'pwd');
		if (password_verify($postpwd,$msg['pwd']) === true) {
			//パスワードがあってたら
			$sqli ="SELECT * FROM $edittable WHERE $idk = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbsline = $posts->fetch() ) {
				$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbsline;
				$var_b += array('oya'=>$oya);
			}
			$var_b += array('message'=>'編集モード...');
		} elseif ($admin_pass == $postpwd ) {
			//管理者編集モード
			$sqli ="SELECT * FROM $edittable WHERE $idk = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbsline = $posts->fetch() ) {
				$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbsline;
				$var_b += array('oya'=>$oya);
			}
			$var_b += array('message'=>'管理者編集モード...');
		} else {
			$db = null; 
			$msgs = null;
			$msg = null;//db切断 
			error('パスワードまたは記事番号が違います。');
		}
		$db = null; 
		$msgs = null;
		$posts = null;
		$msg = null;//db切断 

		$var_b += array('othermode'=>'edit'); //編集モード
		echo $blade->run(OTHERFILE,$var_b);
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//編集モードくん本体
function editexec(){
	global $name,$com,$sub,$picfile,$mail,$url,$pwd,$pwdh,$exid;
	global $badip;
	global $req_method;
	global $blade,$var_b;

	//CSRFトークンをチェック
	if(CHECK_CSRF_TOKEN){
		check_csrf_token();
	}

	$resedit = trim(filter_input(INPUT_POST, 'resedit'));
	$e_no = trim(filter_input(INPUT_POST, 'e_no'));

	if($req_method !== "POST") {error(MSG006);}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com,$name,$mail,$url,$sub);

	if(USE_NAME&&!$name) {error(MSG009);}
	//レスの時は本文必須
	if(filter_input(INPUT_POST, 'modid')&&!$com) {error(MSG008);}
	if(USE_COM&&!$com) {error(MSG008);}
	if(USE_SUB&&!$sub) {error(MSG010);}

	if(strlen($com) > MAX_COM) {error(MSG011);}
	if(strlen($name) > MAX_NAME) {error(MSG012);}
	if(strlen($mail) > MAX_EMAIL) {error(MSG013);}
	if(strlen($sub) > MAX_SUB) {error(MSG014);}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach($badip as $value){ //拒絶host
		if(preg_match("/$value$/i",$host)) {error(MSG016);}
	}
	//↑セキュリティ関連ここまで

	// URLとメールにリンク
	if(AUTOLINK) $com = auto_link($com);
	//ハッシュタグ
	if(USE_HASHTAG) $com = hashtag_link($com);
	// '>'色設定
	$com = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1".RE_START."\\2".RE_END, $com);

	// 連続する空行を一行
	$com = preg_replace("/\n((　| )*\n){3,}/","\n\n",$com);
	//改行をタグに
	if(TH_XHTML == 1){
		//<br />に
		$com = nl2br($com);
	} else {
		//<br>に
		$com = nl2br($com, false);
	}	

	if($resedit == 1) {
		$edittable = 'tabletree';
		$eid = 'iid';
	} else {
		$edittable = 'tablelog';
		$eid = 'tid';
	}

	try {
		$db = new PDO("sqlite:rois.db");
		$sql = "UPDATE $edittable set modified = datetime('now', 'localtime'), name = '$name', mail = '$mail', sub = '$sub', com = '$com', url = '$url', host = '$host', exid = '$exid', pwd = '$pwdh' where $eid = $e_no";
		$db = $db->exec($sql);
		$db = null;
		$var_b += array('message'=>'編集完了しました。');
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	unset($name,$mail,$sub,$com,$url,$pwd,$pwdh,$resto,$pictmp,$picfile,$mode);
	//header('Location:'.PHP_SELF);
	ok('編集に成功しました。画面を切り替えます。');
}

//管理モードin
function admin_in() {
	global $blade,$var_b;
	//$var_b += array('self'=>PHP_SELF);
	$var_b += array('othermode'=>'admin_in');

	echo $blade->run(OTHERFILE,$var_b);
}

//管理モード
function admin() {
	global $admin_pass;
	global $blade,$var_b;

	$var_b += array('path'=>IMG_DIR);

	//最大何ページあるのか
	//記事呼び出しから
	try {
		$db = new PDO("sqlite:rois.db");
		//読み込み
		$adminpass = filter_input(INPUT_POST, 'adminpass');
		if ($adminpass == $admin_pass) {
			$sql = "SELECT * FROM tablelog ORDER BY age DESC,tree DESC";
			$oya = array();
			$posts = $db->query($sql);
			while ($bbsline = $posts->fetch() ) {
				if(empty($bbsline)){break;} //スレがなくなったら抜ける
				$oid = $bbsline["tid"]; //スレのtid(親番号)を取得
				$bbsline['com'] = htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5);
				$oya[] = $bbsline;
			} 
			$var_b += array('oya'=>$oya);

			//スレッドの記事を取得
			$sqli = "SELECT * FROM tabletree ORDER BY tree DESC";
			$ko = array();
			$postsi = $db->query($sqli);
			while ($res = $postsi->fetch()){
				$res['com'] = htmlentities($res['com'],ENT_QUOTES | ENT_HTML5);
				$ko[] = $res;
			}
			$var_b += array('ko'=>$ko);
			echo $blade->run(ADMINFILE,$var_b);
		} else {
			$db = null; //db切断
			error('管理パスを入力してください');
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

// コンティニュー認証 レス画像には非対応
function usrchk(){
	$no = filter_input(INPUT_POST, 'no',FILTER_VALIDATE_INT);
	$pwdf = filter_input(INPUT_POST, 'pwd');
	$flag = FALSE;
	try {
		$db = new PDO("sqlite:rois.db");
		//パスワードを取り出す
		$sql ="SELECT pwd FROM tablelog WHERE tid = $no";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg = $msgs->fetch();
		if(password_verify($pwdf,$msg['pwd'])||substr(md5($pwdf),2,8) === $msg['pwd']){
			$flag = true;
		} else {
			$flag = false;
		}
		$db = null; //切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	if(!$flag) {
		error(MSG028);
	}
}

//OK画面
function ok($mes) {
	global $blade,$var_b;
	$var_b += array('okmes'=>$mes);
	$var_b += array('othermode'=>'ok');
	echo $blade->run(OTHERFILE,$var_b);
}

//エラー画面
function error($mes) {
	global $db;
	global $blade,$var_b;
	$db = null; //db切断
	$var_b += array('errmes'=>$mes);
	$var_b += array('othermode'=>'err');
	echo $blade->run(OTHERFILE,$var_b);
	exit;
}

//画像差し替え失敗
function error2() {
	global $db;
	global $blade,$var_b;
	$db = null; //db切断
	$var_b += array('othermode'=>'err2');
	echo $blade->run(OTHERFILE,$var_b);
	exit;
}

//初期設定

function init(){
	try {
		if (!is_file('rois.db')) {
			// はじめての実行なら、テーブルを作成
			$db = new PDO("sqlite:rois.db");
			$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  
			$sql = "CREATE TABLE tablelog (tid integer primary key autoincrement, created timestamp, modified TIMESTAMP, name VARCHAR(1000), mail VARCHAR(1000), sub VARCHAR(1000), com VARCHAR(10000), url VARCHAR(1000), host TEXT, exid TEXT, id TEXT, pwd TEXT, utime INT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, time TEXT, tree BIGINT, parent INT, age INT, invz VARCHAR(1))";
			$db = $db->query($sql);
			$db = null; //db切断
			$db = new PDO("sqlite:rois.db");
			$sql = "CREATE TABLE tabletree (iid integer primary key autoincrement, tid INT, created timestamp, modified TIMESTAMP, name VARCHAR(1000), mail VARCHAR(1000), sub VARCHAR(1000), com VARCHAR(10000), url VARCHAR(1000), host TEXT, exid TEXT, id TEXT, pwd TEXT, utime INT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, time TEXT, tree BIGINT, parent INT, invz VARCHAR(1))";
			$db = $db->query($sql);
			$db = null; //db切断
		} else {
			$db = new PDO("sqlite:rois.db");
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	$err='';
	if(!is_writable(realpath("./")))error("カレントディレクトリに書けません<br>");
	if(!is_dir(realpath(IMG_DIR))){
		mkdir(IMG_DIR,0707);chmod(IMG_DIR,0707);
	}
	if(!is_dir(realpath(IMG_DIR)))$err.=IMG_DIR."がありません<br>";
	if(!is_writable(realpath(IMG_DIR)))$err.=IMG_DIR."を書けません<br>";
	if(!is_readable(realpath(IMG_DIR)))$err.=IMG_DIR."を読めません<br>";

	if(!is_dir(realpath(TEMP_DIR))){
		mkdir(TEMP_DIR,0707);chmod(TEMP_DIR,0707);
	}
	if(!is_dir(realpath(TEMP_DIR)))$err.=TEMP_DIR."がありません<br>";
	if(!is_writable(realpath(TEMP_DIR)))$err.=TEMP_DIR."を書けません<br>";
	if(!is_readable(realpath(TEMP_DIR)))$err.=TEMP_DIR."を読めません<br>";
	if($err)error($err);
}

/* テンポラリ内のゴミ除去 */
function deltemp(){
	$handle = opendir(TEMP_DIR);
	while ($file = readdir($handle)) {
		if(!is_dir($file)) {
			$lapse = time() - filemtime(TEMP_DIR.$file);
			if($lapse > (TEMP_LIMIT*24*3600)){
				unlink(TEMP_DIR.$file);
			}
			//pchアップロードペイントファイル削除
			if(preg_match("/\A(pchup-.*-tmp\.s?pch)\z/i",$file)) {
				$lapse = time() - filemtime(TEMP_DIR.$file);
				if($lapse > (300)){//5分
					unlink(TEMP_DIR.$file);
				}
			}
		}
	}
	closedir($handle);
}

// 文字コード変換 
function charconvert($str){
	mb_language(LANG);
		return mb_convert_encoding($str, "UTF-8", "auto");
}

/* NGワードがあれば拒絶 */
function Reject_if_NGword_exists_in_the_post($com,$name,$email,$url,$sub){
	global $badstring,$badname,$badstr_A,$badstr_B,$pwd,$admin_pass;
	//チェックする項目から改行・スペース・タブを消す
	$chk_com  = preg_replace("/\s/u", "", $com );
	$chk_name = preg_replace("/\s/u", "", $name );
	$chk_email = preg_replace("/\s/u", "", $email );
	$chk_sub = preg_replace("/\s/u", "", $sub );

	//本文に日本語がなければ拒絶
	if (USE_JAPANESEFILTER) {
		mb_regex_encoding("UTF-8");
		if (strlen($com) > 0 && !preg_match("/[ぁ-んァ-ヶー一-龠]+/u",$chk_com)) error(MSG035);
	}

	//本文へのURLの書き込みを禁止
	if(!($pwd===$admin_pass)){//どちらも一致しなければ
		if(DENY_COMMENTS_URL && preg_match('/:\/\/|\.co|\.ly|\.gl|\.net|\.org|\.cc|\.ru|\.su|\.ua|\.gd/i', $com)) error(MSG036);
	}

	// 使えない文字チェック
	if (is_ngword($badstring, [$chk_com, $chk_sub, $chk_name, $chk_email])) {
		error(MSG032);
	}

	// 使えない名前チェック
	if (is_ngword($badname, $chk_name)) {
		error(MSG037);
	}

	//指定文字列が2つあると拒絶
	$bstr_A_find = is_ngword($badstr_A, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	$bstr_B_find = is_ngword($badstr_B, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	if($bstr_A_find && $bstr_B_find){
		error(MSG032);
	}
}

/**
 * NGワードチェック
 * @param $ngwords
 * @param string|array $strs
 * @return bool
 */
function is_ngword ($ngwords, $strs) {
	if (empty($ngwords)) {
		return false;
	}
	if (!is_array($strs)) {
		$strs = [$strs];
	}
	foreach ($strs as $str) {
		foreach($ngwords as $ngword){//拒絶する文字列
			if ($ngword !== '' && preg_match("/{$ngword}/ui", $str)){
				return true;
			}
		}
	}
	return false;
}

/**
 * 描画時間を計算
 * @param $starttime
 * @return string
 */
function calcPtime ($psec) {

	$D = floor($psec / 86400);
	$H = floor($psec % 86400 / 3600);
	$M = floor($psec % 3600 / 60);
	$S = $psec % 60;

	return
		($D ? $D . PTIME_D : '')
		. ($H ? $H . PTIME_H : '')
		. ($M ? $M . PTIME_M : '')
		. ($S ? $S . PTIME_S : '');
}
