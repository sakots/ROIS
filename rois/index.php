<?php
//--------------------------------------------------
//  おえかきけいじばん「ROIS」
//  by sakots & OekakiBBS reDev.Team  https://dev.oekakibbs.net/
//--------------------------------------------------

//スクリプトのバージョン
define('ROIS_VER','v1.0.0'); //lot.211024.0

//設定の読み込み
require(__DIR__.'/config.php');
require(__DIR__.'/templates/'.THEMEDIR.'/template_ini.php');

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//phpのバージョンが古い場合動かさせない
if (($phpver = phpversion()) < "5.5.0") {
	die("PHP version 5.5.0 or higher is required for this program to work. <br>\n(Current PHP version:{$phpver})");
}
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONF_VER < 9999 || !defined('CONF_VER')) {
	die("コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.");
}

//管理パスが初期値(kanripass)の場合は動作させない
if ($admin_pass === 'kanripass') {
	die("管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.");
}

//BladeOne v3.52
include (__DIR__.'/blade/lib/BladeOne.php');
use eftec\bladeone\BladeOne;

$views = __DIR__.'/templates/'.THEMEDIR; // テンプレートフォルダ
$cache = __DIR__.'/cache'; // キャッシュフォルダ
$blade = new BladeOne($views,$cache,BladeOne::MODE_AUTO); // MODE_DEBUGだと開発モード MODE_AUTOが速い。
$blade->pipeEnable = true; // パイプのフィルターを使えるようにする

$dat = array(); // bladeに格納する変数

//var_dump($_POST);

//絶対パス取得
$path = realpath("./").'/'.IMG_DIR;
$temppath = realpath("./").'/'.TEMP_DIR;

define('IMG_PATH', $path);
define('TMP_PATH', $temppath);

$message = "";
$self = PHP_SELF;

$dat['path'] = IMG_DIR;

$dat['ver'] = ROIS_VER;
$dat['base'] = BASE;
$dat['btitle'] = TITLE;
$dat['home'] = HOME;
$dat['self'] = PHP_SELF;
$dat['message'] = $message;
$dat['pdefw'] = PDEF_W;
$dat['pdefh'] = PDEF_H;
$dat['pmaxw'] = PMAX_W;
$dat['pmaxh'] = PMAX_H;
$dat['themedir'] = THEMEDIR;
$dat['tname'] = THEME_NAME;
$dat['tver'] = THEME_VER;

$dat['use_shi_p'] = USE_SHI_PAINTER;
$dat['use_chicken'] = USE_CHICKENPAINT;

$dat['select_palettes'] = USE_SELECT_PALETTES;
$dat['pallets_dat'] = $pallets_dat;

$dat['dispid'] = DISP_ID;
$dat['updatemark'] = UPDATE_MARK;
$dat['use_resub'] = USE_RESUB;

$dat['useanime'] = USE_ANIME;
$dat['defanime'] = DEF_ANIME;
$dat['use_continue'] = USE_CONTINUE;
$dat['newpost_nopassword'] = !CONTINUE_PASS;

$dat['use_name'] = USE_NAME;
$dat['use_com'] = USE_COM;
$dat['use_sub'] = USE_SUB;

$dat['addinfo'] = $addinfo;

$dat['dptime'] = DSP_PAINTTIME;

$dat['share_button'] = SHARE_BUTTON;

$dat['use_hashtag'] = USE_HASHTAG;

defined('A_NAME_SAN') or define('A_NAME_SAN','さん');
defined('SODANE') or define('SODANE','そうだね');
$dat['sodane'] = SODANE;

//ペイント画面の$pwdの暗号化
defined('CRYPT_PASS') or define('CRYPT_PASS','qRyFf1V6nyU4gSi');
define('CRYPT_METHOD','aes-128-cbc');
define('CRYPT_IV','T3pkYxNyjN7Wz3pu');//半角英数16文字

//テーマがXHTMLか設定されてないなら
defined('TH_XHTML') or define('TH_XHTML', 0);

//日付フォーマット
defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y/m/d H:i:s');

//CheerpJ
define('CHEERPJ_URL', 'https://cjrtnc.leaningtech.com/2.2/loader.js');
$dat['cheerpj'] = CHEERPJ_URL;

//データベース接続PDO
define('DB_PDO', 'sqlite:'.DB_NAME.'.db');

//初期設定(初期設定後は不要なので削除可)
init();

deltemp();

$message ="";

//var_dump($_COOKIE);

$pwdc = filter_input(INPUT_COOKIE, 'pwdc');
$usercode = filter_input(INPUT_COOKIE, 'usercode');//nullならuser-codeを発行

//$_SERVERから変数を取得
//var_dump($_SERVER);

$req_method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"]: "";
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

$dat['usercode'] = $usercode;

//var_dump($_GET);

/*-----------mode-------------*/

$mode = filter_input(INPUT_POST, 'mode');

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
if(filter_input(INPUT_GET, 'mode')==="pictmp"){
	$mode = "pictmp";
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
		$rep = "";
		return paintform($rep);
	case 'piccom':
		$tmpmode = "";
		return paintcom($tmpmode);
	case 'pictmp':
		$tmpmode = "tmp";
		return paintcom($tmpmode);
	case 'anime':
		if(!isset($sp)){$sp="";}
		return openpch($pch,$sp);
	case 'continue':
		return incontinue($no);
	case 'contpaint':
		//パスワードが必要なのは差し換えの時だけ
		$type = filter_input(INPUT_POST,'type');
		if(CONTINUE_PASS||$type==='rep') usrchk();
		// if(ADMIN_NEWPOST) $admin=$pwd;
		$rep = $type;
		return paintform($rep);
	case 'picrep':
		return picreplace();
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

/*-----------Main-------------*/

function init(){
	try {
		if (!is_file(DB_NAME.'.db')) {
			// はじめての実行なら、テーブルを作成
			$db = new PDO(DB_PDO);
			$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE tablelog (tid integer primary key autoincrement, created timestamp, modified TIMESTAMP, name VARCHAR(1000), mail VARCHAR(1000), sub VARCHAR(1000), com VARCHAR(10000), url VARCHAR(1000), host TEXT, exid TEXT, id TEXT, pwd TEXT, utime INT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, time TEXT, tree BIGINT, parent INT, age INT, invz VARCHAR(1), tool TEXT, ext01 TEXT, ext02 TEXT, ext03 TEXT, ext04 TEXT)";
			$db = $db->query($sql);
			$db = null; //db切断
			$db = new PDO(DB_PDO);
			$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE tabletree (iid integer primary key autoincrement, tid INT, created timestamp, modified TIMESTAMP, name VARCHAR(1000), mail VARCHAR(1000), sub VARCHAR(1000), com VARCHAR(10000), url VARCHAR(1000), host TEXT, exid TEXT, id TEXT, pwd TEXT, utime INT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, time TEXT, tree BIGINT, parent INT, invz VARCHAR(1), tool TEXT, ext01 TEXT, ext02 TEXT, ext03 TEXT, ext04 TEXT)";
			$db = $db->query($sql);
			$db = null; //db切断
		} else {
			$db = new PDO(DB_PDO);
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	$err='';
	if(!is_writable(realpath("./")))error("カレントディレクトリに書けません<br>");
	if(!is_dir(IMG_DIR)){
		mkdir(IMG_DIR,PERMISSION_FOR_DIR);chmod(IMG_DIR,PERMISSION_FOR_DIR);
	}
	if(!is_dir(IMG_DIR))$err.=IMG_DIR."がありません<br>";
	if(!is_writable(IMG_DIR))$err.=IMG_DIR."を書けません<br>";
	if(!is_readable(IMG_DIR))$err.=IMG_DIR."を読めません<br>";

	if(!is_dir(TEMP_DIR)){
		mkdir(TEMP_DIR,PERMISSION_FOR_DIR);chmod(TEMP_DIR,PERMISSION_FOR_DIR);
	}
	if(!is_dir(TEMP_DIR))$err.=TEMP_DIR."がありません<br>";
	if(!is_writable(TEMP_DIR))$err.=TEMP_DIR."を書けません<br>";
	if(!is_readable(TEMP_DIR))$err.=TEMP_DIR."を読めません<br>";
	if($err)error($err);
}


//投稿があればデータベースへ保存する
/* 記事書き込み */
function regist() {
	global $badip, $usercode;
	global $req_method;
	global $dat;

	//CSRFトークンをチェック
	if(CHECK_CSRF_TOKEN){
		check_csrf_token();
	}

	$sub = filter_input(INPUT_POST, 'sub');
	$name = filter_input(INPUT_POST, 'name');
	$mail = filter_input(INPUT_POST, 'mail');
	$url = filter_input(INPUT_POST, 'url');
	$com = filter_input(INPUT_POST, 'com');
	$parent = trim(filter_input(INPUT_POST, 'parent'));
	$picfile = filter_input(INPUT_POST, 'picfile');
	$invz = trim(filter_input(INPUT_POST, 'invz'));
	$img_w = trim(filter_input(INPUT_POST, 'img_w',FILTER_VALIDATE_INT));
	$img_h = trim(filter_input(INPUT_POST, 'img_h',FILTER_VALIDATE_INT));
	$pwd = filter_input(INPUT_POST, 'pwd');
	$pwdh = password_hash($pwd,PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid',FILTER_VALIDATE_INT));
	$pal = filter_input(INPUT_POST, 'palettes');

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

	try {
		$db = new PDO(DB_PDO);
		if (isset($_POST["send"] )) {

			$strlen_com = strlen($com);

			if ( $name   === "" ) $name = DEF_NAME;
			if ( $com  === "" ) $com  = DEF_COM;
			if ( $sub  === "" ) $sub  = DEF_SUB;

			$utime = time();
			if ($parent == 0 ) {
				$parent = $utime;
			}
			$tree = ($parent * 1000000000) - $utime;

			// 二重投稿チェック
			if (empty($_POST["modid"])) {
				// スレ立ての場合
				$table = 'tablelog';
				$wid = 'tid';
			} else {
				// レスの場合
				$table = 'tabletree';
				$wid = 'iid';
			}
			//最新コメント取得
			$sqlw = "SELECT * FROM $table ORDER BY $wid DESC LIMIT 1";
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
				}
				//スレ立て時、画像番号が一致の場合(投稿してブラウザバック、また投稿とか)
				//二重投稿と判別(画像がない場合は処理しない)
				if(!empty($_POST["modid"])) {
					if($msgwc["picfile"] !== "" && $picfile == $msgwc["picfile"]){
						$db = null; //db切断
						error('二重投稿ですか？');
					}
				}
			}
			//↑ 二重投稿チェックおわり

			//画像ファイルとか処理
			if ($picfile) {
				$path_filename = pathinfo($picfile, PATHINFO_FILENAME );//拡張子除去
				$fp = fopen(TEMP_DIR.$path_filename.".dat", "r");
				$userdata = fread($fp, 1024);
				fclose($fp);
				list($uip,$uhost,,,$ucode,,$starttime,$postedtime,$uresto,$tool) = explode("\t", rtrim($userdata)."\t");
				//描画時間を$userdataをもとに計算
				if($starttime && DSP_PAINTTIME){
					$psec = $postedtime - $starttime; //内部保存用
					$time = calcPtime($psec);
				}
				//ツール
				if( $tool === 'neo') {
					$used_tool = 'PaintBBS NEO';
				} elseif ( $tool === 'shi') {
					$used_tool = 'Shi Painter';
				} elseif ( $tool === 'chicken' ) {
					$used_tool = 'Chicken Paint';
				} else {
					$used_tool = '???';
				}
				list($img_w,$img_h) = getimagesize(TEMP_DIR.$picfile);
				rename( TEMP_DIR.$picfile , IMG_DIR.$picfile );
				chmod( IMG_DIR.$picfile , PERMISSION_FOR_DEST);

				$picdat = $path_filename.'.dat';

				$chifile = $path_filename.'.chi';
				$spchfile = $path_filename.'.spch';
				$pchfile = $path_filename.'.pch';

				if ( is_file(TEMP_DIR.$pchfile) ) {
					rename( TEMP_DIR.$pchfile, IMG_DIR.$pchfile );
					chmod( IMG_DIR.$pchfile , PERMISSION_FOR_DEST);
				} elseif( is_file(TEMP_DIR.$spchfile) ) {
					rename( TEMP_DIR.$spchfile, IMG_DIR.$spchfile );
					chmod( IMG_DIR.$spchfile , PERMISSION_FOR_DEST);
					$pchfile = $spchfile;
				} elseif( is_file(TEMP_DIR.$chifile) ) {
					rename( TEMP_DIR.$chifile, IMG_DIR.$chifile );
					chmod( IMG_DIR.$chifile, PERMISSION_FOR_DEST);
					$pchfile = $chifile;
				} else {
					$pchfile = "";
				}
				chmod( TEMP_DIR.$picdat, PERMISSION_FOR_DEST );
				unlink( TEMP_DIR.$picdat );
			} else {
				$img_w = 0;
				$img_h = 0;
				$pchfile = "";
				$time = "";
				$used_tool = "";
			}

			// 値を追加する
			// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
			$name = str_replace("'","''",$name);
			$sub = str_replace("'","''",$sub);
			$com = str_replace("'","''",$com);
			$mail = str_replace("'","''",$mail);
			$url = str_replace("'","''",$url);
			$host = str_replace("'","''",$host);

			//不要改行圧縮
			$com = preg_replace("/(\n|\r|\r\n){3,}/us", "\n\n", $com);

			//id生成
			$id = gen_id($host, $utime);

			// スレ建ての場合
			if (empty($_POST["modid"]) === true) {
				$age = 0;
				$sql = "INSERT INTO tablelog (created, modified, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, age, invz, host, tool) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$psec', '$pwdh', '$id', '$exid', '$tree', '$age', '$invz', '$host', '$used_tool')";
				$db->exec($sql);
			} elseif(empty($_POST["modid"]) !== true && strpos($mail,'sage') !== false ) {
				//レスの場合でメール欄にsageが含まれる
				$tid = filter_input(INPUT_POST, 'modid');

				$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host, tool) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$psec', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host', '$used_tool')";
				$db = $db->exec($sql);
			} else {
				//レスの場合でメール欄にsageが含まれない
				$tid = filter_input(INPUT_POST, 'modid');
				//age処理するかどうか
				//スレのレス数を数える
				$sqlr = "SELECT COUNT('iid') as cntres FROM tabletree WHERE tid = $tid AND invz=0";
				$countsr = $db->query("$sqlr");
				$countr = $countsr->fetch();
				$resn = $countr["cntres"]; //スレのレス数取得できた

				//レス数カウント
				$sql = "SELECT COUNT('iid') as cnt FROM tabletree WHERE tid = $tid";
				$counts = $db->query("$sql");
				$count = $counts->fetch();
				$age = $count["cnt"];

				//レス数が指定値より少ないならage
				if($resn < MAX_RES){
					$nage = $age +1;
					$tree = time() * 999999999;
					$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host, tool) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$psec', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host', '$used_tool'); UPDATE tablelog set age = '$nage', tree = '$tree' where tid = '$tid'";
				} else {
					$sql = "INSERT INTO tabletree (created, modified, tid, name, sub, com, mail, url, picfile, pchfile, img_w, img_h, utime, parent, time, pwd, id, exid, tree, invz, host, tool) VALUES (datetime('now', 'localtime') , datetime('now', 'localtime') , '$tid', '$name', '$sub', '$com', '$mail', '$url', '$picfile', '$pchfile', '$img_w', '$img_h', '$utime', '$parent', '$psec', '$pwdh', '$id', '$exid', '$tree', '$invz', '$host', '$used_tool')";
				}
				$db = $db->exec($sql);
			}
			$c_pass = $pwd;
			//-- クッキー保存 --
			//クッキー項目："クッキー名 クッキー値"
			$cookies = ["namec\t".$name,"emailc\t".$mail,"urlc\t".$url,"pwdc\t".$c_pass."palettec\t".$pal];

			foreach ( $cookies as $cookie ) {
				list($c_name,$c_cookie) = explode("\t",$cookie);
				setcookie ($c_name, $c_cookie,time()+(SAVE_COOKIE*24*3600));
			}

			$dat['message'] = '書き込みに成功しました。';
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
	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sqlth = "SELECT COUNT(*) as cnt FROM tablelog";
		$countth = $db->query("$sqlth");
		$countth = $countth->fetch();
		$th_cnt = $countth["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	if($th_cnt > LOG_MAX_T) {
		logdel();
	}

	ok('書き込みに成功しました。画面を切り替えます。');
}

//通常表示モード
function def() {
	global $dat,$blade;
	$dsp_res = DSP_RES;
	$page_def = PAGE_DEF;

	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sqlth = "SELECT COUNT(*) as cnt FROM tablelog";
		$countth = $db->query("$sqlth");
		$countth = $countth->fetch();
		$th_cnt = $countth["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	if($th_cnt > LOG_MAX_T) {
		logdel();
	}

	//古いスレのレスボタンを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['nowtime'] = $nowtime;
	$dat['elapsed_time'] = $elapsed_time;

	//ページング
	try {
		$db = new PDO(DB_PDO);
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
		$dat['max_page'] = $max_page;

		//リンク作成用
		$dat['nowpage'] = $page;
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$dat['paging'] = $paging;
		$dat['pp'] = $pp;

		$dat['back'] = ($page - 1);

		$dat['next'] = ($page + 1);

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
		$dat['m_tid'] = $m_tid; //テーマのほうでこれから親idを引く
		// →「スレの古さ番号」が出る。大きいほど古い。
		//閾値を考える
		$thid = LOG_MAX_T * LOG_LIMIT/100; //閾値
		$dat['thid'] = $thid;
		//テーマのほうでこの数字と「スレの古さ番号」を比べる
		//thidよりスレの古さ番号が大きいスレは消えるリミットフラグが立つ

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//読み込み

	try {
		$db = new PDO(DB_PDO);
		//1ページの全スレッド取得
		$sql = "SELECT * FROM tablelog WHERE invz=0 ORDER BY tree DESC LIMIT $start,$page_def";
		$posts = $db->query($sql);

		$ko = array();
		$oya = array();

		$i = 0;
		$j = 0;
		while ( $i < PAGE_DEF) {
			$bbsline = $posts->fetch();
			if(empty($bbsline)){break;} //スレがなくなったら抜ける
			$oid = $bbsline["tid"]; //スレのtid(親番号)を取得
			$sqli = "SELECT * FROM tabletree WHERE tid = $oid and invz=0 ORDER BY tree ASC";
			//レス取得
			$postsi = $db->query($sqli);
			$j = 0;
			$flag = true;
			while ( $flag == true) {
				$bbsline['time'] = is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
				$_pchext = pathinfo($bbsline['pchfile'],PATHINFO_EXTENSION);
				if($_pchext === 'chi'){
					$bbsline['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
				}
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
				$res['com'] = htmlspecialchars($res['com'], ENT_QUOTES | ENT_HTML5 );

				//オートリンク
				if(AUTOLINK) {
					$res['com'] = auto_link($res['com']);
				}
				//ハッシュタグ
				if(USE_HASHTAG) {
					$res['com'] = hashtag_link($res['com']);
				}
				//空行を縮める
				$res['com'] = preg_replace('/(\n|\r|\r\n|\n\r){3,}/us',"\n\n", $res['com']);
				//<br>に
				$res['com'] = tobr($res['com']);
				//引用の色
				$res['com'] = quote($res['com']);
				//日付をUNIX時間に変換して設定どおりにフォーマット
				$res['created'] = date(DATE_FORMAT, strtotime($res['created']));
				$res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
				$ko[] = $res;
				$j++;
			}
			// http、https以外のURLの場合表示しない
			if(!filter_var($bbsline['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['url'])) {
				$bbsline['url'] = "";
			}
			$bbsline['com'] = htmlspecialchars($bbsline['com'], ENT_QUOTES | ENT_HTML5 );

			//オートリンク
			if(AUTOLINK) {
				$bbsline['com'] = auto_link($bbsline['com']);
			}
			//ハッシュタグ
			if(USE_HASHTAG) {
				$bbsline['com'] = hashtag_link($bbsline['com']);
			}
			//空行を縮める
			$bbsline['com'] = preg_replace('/(\n|\r|\r\n){3,}/us',"\n\n", $bbsline['com']);
			//<br>に
			$bbsline['com'] = tobr($bbsline['com']);
			//引用の色
			$bbsline['com'] = quote($bbsline['com']);
			//日付をUNIX時間に
			$bbsline['created'] = date(DATE_FORMAT, strtotime($bbsline['created']));
			$bbsline['modified'] = date(DATE_FORMAT, strtotime($bbsline['modified']));
			$oya[] = $bbsline;
			$i++;
		}

		$dat['ko'] = $ko;
		$dat['oya'] = $oya;
		$dat['dsp_res'] = DSP_RES;
		$dat['path'] = IMG_DIR;

		echo $blade->run(MAINFILE,$dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//カタログモード
function catalog() {
	global $blade,$dat;
	$page_def = CATALOG_N;

	//ページング
	try {
		$db = new PDO(DB_PDO);
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
		$dat['max_page'] = $max_page;

		//リンク作成用
		$dat['nowpage'] = $page;
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$dat['paging'] = $paging;
		$dat['pp'] = $pp;

		$dat['back'] = ($page - 1);

		$dat['next'] = ($page + 1);

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//読み込み

	try {
		$db = new PDO(DB_PDO);
		//1ページの全スレッド取得
		$sql = "SELECT tid, created, modified, name, mail, sub, com, url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, time, tree, parent, age, utime FROM tablelog WHERE invz=0 ORDER BY age DESC, tree DESC LIMIT $start,$page_def";
		$posts = $db->query($sql);

		$oya = array();

		$i = 0;
		while ( $i < CATALOG_N) {
			$bbsline = $posts->fetch();
			if(empty($bbsline)){break;} //スレがなくなったら抜ける
			$bbsline['com'] = nl2br(htmlspecialchars($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$i++;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		//$smarty->debugging = true;
		$dat['catalogmode'] = 'catalog';
		echo $blade->run(CATALOGFILE,$dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//検索モード 現在全件表示のみ対応
function search() {
	global $blade,$dat;

	$searchf = filter_input(INPUT_GET, 'search');
	$search = str_replace("'","''",$searchf); //SQL
	//部分一致検索
	$bubun =  filter_input(INPUT_GET, 'bubun');
	//本文検索
	$tag = filter_input(INPUT_GET, 'tag');

	//読み込み
	try {
		$db = new PDO(DB_PDO);
		//全スレッド取得
		//まずtagがあれば本文検索
		if ($tag == 'tag') {
			$sql = "SELECT * FROM tablelog WHERE com LIKE '%$search%' AND invz=0 ORDER BY age DESC, tree DESC";
			//レスも
			$sqli = "SELECT * FROM tabletree WHERE com LIKE '%$search%' and invz=0 ORDER BY tree ASC";
			$dat['catalogmode'] = 'hashsearch';
			$dat['tag'] = $searchf;
		} else {
			//tagがなければ作者名検索
			if($bubun == "bubun"){
				$sql = "SELECT * FROM tablelog WHERE name LIKE '%$search%' AND invz=0 ORDER BY age DESC, tree DESC";
			} else {
				$sql = "SELECT * FROM tablelog WHERE name LIKE '$search' AND invz=0 ORDER BY age DESC, tree DESC";
			}
			$dat['catalogmode'] = 'search';
			$dat['author'] = $searchf;
		}

		$posts = $db->query($sql);

		$oya = array();

		$i = 0;
		while ($bbsline = $posts->fetch()) {
			$bbsline['com'] = nl2br(htmlspecialchars($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
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
			$dat['ko'] = $ko;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		//$smarty->debugging = true;
		$dat['s_result'] = $i;
		echo $blade->run(CATALOGFILE,$dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//そうだね
function sodane(){
	$resto = filter_input(INPUT_GET, 'resto');
	try {
		$db = new PDO(DB_PDO);
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
		$db = new PDO(DB_PDO);
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
	global $blade,$dat;
	$resno = filter_input(INPUT_GET, 'res');
	$dat['resno'] = $resno;

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//古いスレのレスフォームを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['elapsed_time'] = $elapsed_time;
	$dat['nowtime'] = $nowtime;

	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tablelog WHERE tid = $resno ORDER BY tree DESC";
		$posts = $db->query($sql);

		$oya = array();
		$ko = array();
		while ($bbsline = $posts->fetch() ) {
			$bbsline['time']=is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
			//スレッドの記事を取得
			$sqli = "SELECT * FROM tabletree WHERE (invz = 0 AND tid = $resno ) ORDER BY tree ASC";
			$postsi = $db->query($sqli);
			$rresname = array();
			while ($res = $postsi->fetch()){
				$res['com'] = htmlspecialchars($res['com'],ENT_QUOTES | ENT_HTML5);

				if(AUTOLINK) {
					$res['com'] = auto_link($res['com']);
				}
				//ハッシュタグ
				if(USE_HASHTAG) {
					$res['com'] = hashtag_link($res['com']);
				}
				//空行を縮める
				$res['com'] = preg_replace('/(\n|\r|\r\n){3,}/us',"\n\n", $res['com']);
				//<br>に
				$res['com'] = tobr($res['com']);
				//引用の色
				$res['com'] = quote($res['com']);
				//日付をUNIX時間に
				$res['created'] = date(DATE_FORMAT, strtotime($res['created']));
				$res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
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
			$bbsline['com'] = htmlspecialchars($bbsline['com'],ENT_QUOTES | ENT_HTML5);

			if(AUTOLINK) {
				$bbsline['com'] = auto_link($bbsline['com']);
			}
			//ハッシュタグ
			if(USE_HASHTAG) {
				$bbsline['com'] = hashtag_link($bbsline['com']);
			}
			//空行を縮める
			$bbsline['com'] = preg_replace('/(\n|\r|\r\n){3,}/us',"\n", $bbsline['com']);
			//<br>に
			$bbsline['com'] = tobr($bbsline['com']);
			//引用の色
			$bbsline['com'] = quote($bbsline['com']);
			//日付をUNIX時間に
			$bbsline['created'] = date(DATE_FORMAT, strtotime($bbsline['created']));
			$bbsline['modified'] = date(DATE_FORMAT, strtotime($bbsline['modified']));
			$oya[] = $bbsline;
			if (!in_array($bbsline['name'], $rresname)) {
				$rresname[] = $bbsline['name'];
			}
			// http、https以外のURLの場合表示しない
			if(!filter_var($bbsline['url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['url'])) {
				$bbsline['url'] = "";
			}
			//名前付きレス用
			$resname = implode(A_NAME_SAN.' ',$rresname);
			$dat['resname'] = $resname;

			$dat['oya'] = $oya;
			$dat['ko'] = $ko;
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
		$dat['m_tid'] = $m_tid; //テーマのほうでこれから親idを引く
		// →「スレの古さ番号」が出る。大きいほど古い。
		//閾値を考える
		$thid = LOG_MAX_T * LOG_LIMIT/100; //閾値
		$dat['thid'] = $thid;
		//テーマのほうでこの数字と「スレの古さ番号」を比べる
		//thidよりスレの古さ番号が大きいスレは消えるリミットフラグが立つ
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}

	$dat['path'] = IMG_DIR;

	echo $blade->run(RESFILE,$dat);
}

//お絵描き画面
function paintform($rep){
	global $message,$usercode,$quality,$qualitys,$no;
	global $mode,$ctype,$pch,$type;
	global $blade,$dat;
	global $pallets_dat;

	$pwd = trim(filter_input(INPUT_POST, 'pwd'));
	$imgfile = filter_input(INPUT_POST, 'img');

	//ツール
	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	$dat['message'] = $message;

	$picw = filter_input(INPUT_POST, 'picw',FILTER_VALIDATE_INT);
	$pich = filter_input(INPUT_POST, 'pich',FILTER_VALIDATE_INT);

	if($mode === "contpaint"){
		list($picw,$pich) = getimagesize(IMG_DIR.$imgfile); //キャンバスサイズ

	}

	$anime = isset($_POST["anime"]) ? true : false;
	$dat['anime'] = $anime;

	if($picw < 300) $picw = 300;
	if($pich < 300) $pich = 300;
	if($picw > PMAX_W) $picw = PMAX_W;
	if($pich > PMAX_H) $pich = PMAX_H;

	$dat['picw'] = $picw;
	$dat['pich'] = $pich;

	if($tool == "shi") { //しぃペインターの時の幅と高さ
		$ww = $picw + 510;
		$hh = $pich + 172;
	} else { //NEOのときの幅と高さ
		$ww = $picw + 150;
		$hh = $pich + 172;
	}
	if($hh < 560){$hh = 560;}//共通の最低高
	$dat['w'] = $ww;
	$dat['h'] = $hh;

	$dat['undo'] = UNDO;
	$dat['undo_in_mg'] = UNDO_IN_MG;

	$dat['useanime'] = USE_ANIME;

	$dat['path'] = IMG_DIR;

	$dat['stime'] = time();

	$userip = get_uip();

	//しぃペインター
	$dat['layer_count'] = LAYER_COUNT;
	$qq = $quality ? $quality : $qualitys[0];
	$dat['quality'] = $qq;

	//続きから
	if($rep !== ""){
		$ctype = filter_input(INPUT_POST, 'ctype');
		$type = $rep;
		$pwdf = filter_input(INPUT_POST, 'pwd');

		$dat['no'] = $no;
		$dat['pwd'] = $pwdf;
		$dat['ctype'] = $ctype;
		if(is_file(IMG_DIR.$pch.'.pch')){
			$useneo = true;
			$dat['useneo'] = true;
		}elseif(is_file(IMG_DIR.$pch.'.spch')){
			$useneo = false;
			$dat['useneo'] = false;
		}
		if((C_SECURITY_CLICK || C_SECURITY_TIMER) && SECURITY_URL){
			$dat['security'] = true;
			$dat['security_click'] = C_SECURITY_CLICK;
			$dat['security_timer'] = C_SECURITY_TIMER;
		}
	}else{
		if((SECURITY_CLICK || SECURITY_TIMER) && SECURITY_URL){
			$dat['security'] = true;
			$dat['security_click'] = SECURITY_CLICK;
			$dat['security_timer'] = SECURITY_TIMER;
		}
		$dat['newpaint'] = true;
	}
	$dat['security_url'] = SECURITY_URL;

	//パレット設定
	//初期パレット
	$initial_palette = 'Palettes[0] = "#000000\n#FFFFFF\n#B47575\n#888888\n#FA9696\n#C096C0\n#FFB6FF\n#8080FF\n#25C7C9\n#E7E58D\n#E7962D\n#99CB7B\n#FCECE2\n#F9DDCF";';
	foreach($pallets_dat as $p_value){
		if($p_value[1] == filter_input(INPUT_POST, 'palettes')){ // キーと入力された値が同じなら
			$set_palettec = $p_value[1];
			setcookie("palettec", $set_palettec, time()+(86400*SAVE_COOKIE)); // Cookie保存
			if(is_array($p_value)){
				$lines = file($p_value[1]);
			}else{
				$lines = file($p_value);
			}
			break;
		}
	}

	$pal = array();
	$DynP = array();
	$p_cnt = 0;
	foreach ( $lines as $i => $line ) {
		$line = charconvert(str_replace(["\r","\n","\t"],"",$line));
		list($pid,$pname,$pal[0],$pal[2],$pal[4],$pal[6],$pal[8],$pal[10],$pal[1],$pal[3],$pal[5],$pal[7],$pal[9],$pal[11],$pal[12],$pal[13]) = explode(",", $line);
		$DynP[] = $pname;
		$p_cnt = $i + 1;
		$palettes = 'Palettes['.$p_cnt.'] = "#';
		ksort($pal);
		$palettes .= implode('\n#',$pal);
		$palettes .= '";';
		$arr_pal[$i] = $palettes;
	}
	$user_pallete_i = $initial_palette.implode('',$arr_pal);
	$dat['palettes'] = $user_pallete_i;

	$count_dynp = count($DynP) + 1;

	$dat['palsize'] = $count_dynp;

	//パスワード暗号化
	$pwdf = openssl_encrypt ($pwd,CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV);//暗号化
	$pwdf = bin2hex($pwdf);//16進数に

	foreach ($DynP as $p){
		$arr_dynp[] = '<option>'.$p.'</option>';
	}
	$dat['dynp'] = implode('',$arr_dynp);

	if($ctype=='pch'){
		$pchfile = filter_input(INPUT_POST, 'pch');
		$dat['pchfile'] = IMG_DIR.$pchfile;
	}
	if($ctype=='img'){
		$dat['animeform'] = false;
		$dat['anime'] = false;
		$imgfile = filter_input(INPUT_POST, 'img');
		$dat['imgfile'] = IMG_DIR.$imgfile;
	}
	$usercode.= '&tool='.$tool.'&stime='.time(); //拡張ヘッダにツールと描画開始時間をセット

	//差し換え時の認識コード追加
	if($type === 'rep'){
		$no = filter_input(INPUT_POST, 'no',FILTER_VALIDATE_INT);
		$userip = get_uip();
		$time=time();
		$repcode = substr(crypt(md5($no.$userip.$pwdf.date("Ymd", $time)),$time),-8);
		//念の為にエスケープ文字があればアルファベットに変換
		$repcode = strtr($repcode,"!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~","ABCDEFGHIJKLMNOabcdefghijklmn");
		$datmode = 'picrep&no='.$no.'&pwd='.$pwdf.'&repcode='.$repcode;
		$usercode.='&repcode='.$repcode;
	}
	$dat['usercode'] = $usercode; //usercodeにいろいろくっついたものをまとめて出力

	//出口
	if($type === 'rep') {
		//差し替え
		$dat['mode'] = $datmode;
	} else {
		//新規投稿
		$dat['mode'] = 'piccom';
	}
	//出力
	echo $blade->run(PAINTFILE,$dat);
}

//アニメ再生

function openpch($pch,$sp="") {
	global $blade,$dat;
	$message = "";

	$pch = filter_input(INPUT_GET, 'pch');
	$pchh = str_replace( strrchr($pch,"."), "", $pch); //拡張子除去
	$extn = substr($pch, strrpos($pch, '.') + 1); //拡張子取得

	$picfile = IMG_DIR.$pchh.".png";

	if($extn == 'spch') {
		$pchfile = IMG_DIR.$pch;
		$dat['tool'] = 'shi'; //拡張子がspchのときはしぃぺ
	} elseif($extn == 'pch') {
		$pchfile = IMG_DIR.$pch;
		$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
	//}elseif($extn=='chi'){
	//	$pchfile = IMG_DIR.$pch;
	//	$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint 対応してくれるといいな
	} else {
		$w=$h=$picw=$pich=$datasize=""; //動画が無い時は処理しない
		$dat['tool'] = 'neo';
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

	$dat['picw'] = $picw;
	$dat['pich'] = $pich;
	$dat['w'] = $w;
	$dat['h'] = $h;
	$dat['pchfile'] = './'.$pch;
	$dat['datasize'] = $datasize;

	$dat['speed'] = PCH_SPEED;

	$dat['path'] = IMG_DIR;
	$dat['a_stime'] = time();

	echo $blade->run(ANIMEFILE,$dat);
}

//お絵かき投稿
function paintcom($tmpmode){
	global $usercode,$ptime;
	global $blade,$dat;

	$dat['parent'] = $_SERVER['REQUEST_TIME'];
	$dat['usercode'] = $usercode;

	//----------

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//投稿途中一覧 or 画像新規投稿 or 画像差し替え
	if ($tmpmode == "tmp") {
		$dat['picmode'] = 'is_temp';
	} elseif ($tmpmode == "rep") {
		$dat['picmode'] = 'pict_rep';
	} else {
		$dat['picmode'] = 'pict_up';
	}

	//----------

	//var_dump($_POST);
	$userip = get_uip();
	//テンポラリ画像リスト作成
	$tmplist = array();
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if(!is_dir($file) && preg_match("/\.(dat)\z/i",$file)) {
			$fp = fopen(TEMP_DIR.$file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip,$uhost,$uagent,$imgext,$ucode,,$starttime,$postedtime,,$tool) = explode("\t", rtrim($userdata)."\t");
			$file_name = preg_replace("/\.(dat)\z/i","",$file); //拡張子除去
			if(is_file(TEMP_DIR.$file_name.$imgext)) //画像があればリストに追加
			//描画時間を$userdataをもとに計算
			//(表示用)
			$ptime = calcPtime((int)$postedtime - (int)$starttime);
			//描画時間(内部用)
			$pptime = (int)$postedtime - (int)$starttime;
			$tmplist[] = $ucode."\t".$uip."\t".$file_name.$imgext."\t".$ptime."\t".$pptime."\t".$tool;

		}
	}
	closedir($handle);
	$tmp = array();
	if(count($tmplist)!=0){
		//user-codeとipアドレスでチェック
		foreach($tmplist as $tmpimg){
			list($ucode,$uip,$ufilename,$ptime,$pptime,$tool) = explode("\t", $tmpimg);
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
			$ptime = $ptime;
			$pptime = $pptime;
			$temp[] = compact('src','srcname','date','tool','ptime','pptime');
		}
		$dat['temp'] = $temp;
	}

	$tmp2 = array();
	$dat['tmp'] = $tmp2;

	echo $blade->run(PICFILE,$dat);
}

//コンティニュー画面in
function incontinue($no) {
	global $blade,$dat;
	$dat['othermode'] = 'incontinue';
	$dat['continue_mode'] = true;

	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	//コンティニュー時は削除キーを常に表示
	$dat['passflag'] = true;
	//新規投稿で削除キー不要の時 true
	if(!CONTINUE_PASS) $dat['newpost_nopassword'] = true;

	try{
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tablelog WHERE picfile='$no' ORDER BY tree DESC";
		$posts = $db->query($sql);

		$oya = array();
		while ($bbsline = $posts->fetch() ) {
			$bbsline['time']=is_numeric($bbsline['time']) ? calcPtime($bbsline['time']) : $bbsline['time'];
			$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$dat['oya'] = $oya; //配列に格納
		}
		$hist_ope = pathinfo($no, PATHINFO_FILENAME ); //拡張子除去
		$histfilename = IMG_DIR.$hist_ope;
		if(is_file($histfilename.'.spch')){
			//$pchfile = IMG_DIR.$pch;
			$dat['tool'] = 'shi'; //拡張子がspchのときはしぃぺ
			$dat['useshi'] = true;
			$dat['useneo'] = false;
			$dat['ctype_pch'] = true;
		}elseif(is_file($histfilename.'.pch')){
			//$pchfile = IMG_DIR.$pch;
			$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
			$dat['useshi'] = false;
			$dat['useneo'] = true;
			$dat['ctype_pch'] = true;
		}elseif(is_file($histfilename.'.chi')){
			$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint
			$dat['useshi'] = false;
			$dat['useneo'] = false;
			$dat['ctype_pch'] = true;
		}else { // どれでもない＝動画が無い時
			//$w=$h=$picw=$pich=$datasize="";
			$dat['useneo'] = true;
			$dat['useshi'] = true;
			$dat['ctype_pch'] = false;
		}
		// useshi, useneoは互換のためにいちおう残してある
		$dat['ctype_img'] = true;

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}

	echo $blade->run(OTHERFILE,$dat);
}

//削除くん

function delmode(){
	global $admin_pass;
	global $blade,$dat;
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
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql ="SELECT pwd FROM $deltable WHERE $idk = '$delno'";
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
		$sqlp ="SELECT picfile FROM $deltable WHERE $idk = '$delno'";
		$msgsp = $db->prepare($sqlp);
		$msgsp->execute();
		$msgp = $msgsp->fetch();
		if (empty($msgp)) {
			error('画像が見当たりません。');
		}
		$msgpic = $msgp['picfile']; //画像の名前取得できた

		if (isset($_POST["admindel"])) {
			$admindelmode = 1;
		} else {
			$admindelmode = 0;
		}

		if (password_verify($ppwd,$msg['pwd'])) {
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
				if (is_file(IMG_DIR.$msgdat.'.chi')) {
					unlink(IMG_DIR.$msgdat.'.chi');
				}
			}
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM $deltable WHERE $idk=$delno";
			$db = $db->exec($sql);
			$dat['message'] = '削除しました。';
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
				if (is_file(IMG_DIR.$msgdat.'.chi')) {
					unlink(IMG_DIR.$msgdat.'.chi');
				}
			}
			//↑画像とか削除処理完了
			//データベースから削除
			//スレの場合
			if($delt === 0) {
				$sql = "DELETE FROM tablelog WHERE $idk = '$delno'";
				$db->exec($sql);
				$sql = "DELETE FROM tabletree WHERE tid = '$delno'";
				$db = $db->exec($sql);
			} else {
				//レスの場合
				$sql = "DELETE FROM tabletree WHERE $idk = '$delno'";
				$db = $db->exec($sql);
			}
			$dat['message'] = '削除しました。';
		} elseif ($admin_pass == $ppwd && $admindelmode != 1) {
			//管理モード以外での管理者削除は
			//データベースから削除はせずに非表示
			$sql = "UPDATE $deltable SET invz=1 WHERE $idk = '$delno'";
			$db = $db->exec($sql);
			$dat['message'] = '非表示にしました。';
		} else {
			error('パスワードまたは記事番号が違います。');
		}
		$msgp = null;
		$msg = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	//変数クリア
	unset($delno,$delt);
	//header('Location:'.PHP_SELF);
	ok('削除しました。画面を切り替えます。');
}

//画像差し替え レス画像は非対応
function picreplace(){
	global $type;
	global $path,$badip;
	global $blade,$dat;
	$stime = filter_input(INPUT_GET, 'stime',FILTER_VALIDATE_INT);
	$no = filter_input(INPUT_GET, 'no',FILTER_VALIDATE_INT);
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
	$find = false;
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if(!is_dir($file) && preg_match("/\.(dat)\z/i",$file)) {
			$fp = fopen(TEMP_DIR.$file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($uip,$uhost,$uagent,$imgext,$ucode,$urepcode,$starttime,$postedtime,,$tool) = explode("\t", rtrim($userdata)."\t");//区切りの"\t"を行末にして配列へ格納
			$file_name = pathinfo($file, PATHINFO_FILENAME ); //拡張子除去
			if($file_name && is_file(TEMP_DIR.$file_name.$imgext) && $urepcode === $repcode){
				$find = true;
				break;
			}

		}
	}
	closedir($handle);
	if(!$find){
	error(MSG007);
	}

	// ログ読み込み
	try {
		$db = new PDO(DB_PDO);
		//記事を取り出す
		$sql = "SELECT * FROM tablelog WHERE tid = $no";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg_d = $msgs->fetch();

		//パスワード照合
		// $flag = false;
		if(password_verify($pwdf,$msg_d["pwd"])){
			//パスワードがあってたら画像アップロード処理
			$up_picfile = TEMP_DIR.$file_name.$imgext;
			$dest = IMG_DIR.$stime.'.tmp';
			copy($up_picfile, $dest);

			if(!is_file($dest)) error(MSG003);
			chmod($dest,PERMISSION_FOR_DEST);
			//元ファイル削除
			unlink(IMG_DIR.$msg_d["picfile"]);

			$img_type = mime_content_type($dest);
			$imgext = getImgType($img_type, $dest);

			//新しい画像の名前(DB保存用)
			$new_picfile = $file_name.$imgext;

			chmod($dest,PERMISSION_FOR_DEST);
			rename($dest, IMG_DIR.$new_picfile);

			//ワークファイル削除
			if (is_file($up_picfile)) unlink($up_picfile);
			if (is_file(TEMP_DIR.$file_name.".dat")) unlink(TEMP_DIR.$file_name.".dat");

			//動画ファイルアップロード
			//拡張子チェック
			$pchext='';
			if(is_file(TEMP_DIR.$file_name.'.chi')) {
				$pchext = '.chi';
			} elseif (is_file(TEMP_DIR.$file_name.'.spch')) {
				$pchext = '.spch';
			} elseif (is_file(TEMP_DIR.$file_name.'.pch')) {
				$pchext = '.pch';
			}
			//元ファイル削除
			safe_unlink(IMG_DIR.$msg_d["pchfile"]);

			//新しい動画ファイルの名前(DB保存用)
			$new_pchfile = $file_name.$pchext;

			//動画ファイルアップロード本編
			if(is_file(TEMP_DIR.$file_name.$pchext)) {
				$pchsrc = TEMP_DIR.$file_name.$pchext;
				$dst = IMG_DIR.$new_pchfile;
				if(copy($pchsrc, $dst)){
					chmod($dst,PERMISSION_FOR_DEST);
					unlink($pchsrc);
				}
			}

			//描画時間を$userdataをもとに計算
			$ptime = (int)$msg_d['time']+((int)$postedtime-(int)$starttime);

			//ホスト名取得
			$host = gethostbyaddr(get_uip());

			//id生成
			$id = gen_id($host, $ptime);

			// 念のため'のエスケープ
			$host = str_replace("'","''",$host);

			//db上書き
			$sqlrep = "UPDATE tablelog set modified = datetime('now', 'localtime'), host = '$host', picfile = '$new_picfile', pchfile = '$new_pchfile', id = '$id', time = '$ptime' WHERE tid = '$no'";
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
	global $blade,$dat;

	//csrfトークンをセット
	$dat['token']='';
	if(CHECK_CSRF_TOKEN){
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//入力されたパスワード
	$postpwd = filter_input(INPUT_POST, 'pwd');

	$editno = filter_input(INPUT_POST, 'delno');
	if ($editno == "") {
		error('記事番号を入力してください');
	}
	$editt = filter_input(INPUT_POST, 'delt'); //0親1レス
	if ($editt === 0) {
		$edittable = 'tablelog';
		$idk = "tid";
	} else {
		$edittable = 'tabletree';
		$idk = "iid";
		$dat['resedit'] = 'resedit';
	}
	//記事呼び出し
	try {
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql ="SELECT pwd FROM $edittable WHERE $idk = $editno";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg = $msgs->fetch();
		if (empty($msg)) {
			error('そんな記事ないです。');
		}
		if (password_verify($postpwd,$msg['pwd'])) {
			//パスワードがあってたら
			$sqli ="SELECT * FROM $edittable WHERE $idk = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbsline = $posts->fetch() ) {
				$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbsline;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '編集モード...';
		} elseif ($admin_pass == $postpwd ) {
			//管理者編集モード
			$sqli ="SELECT * FROM $edittable WHERE $idk = $editno";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbsline = $posts->fetch() ) {
				$bbsline['com'] = nl2br(htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbsline;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '管理者編集モード...';
		} else {
			$db = null;
			$msgs = null;
			$db = null; //db切断
			error('パスワードまたは記事番号が違います。');
		}
		$db = null;
		$msgs = null;
		$posts = null;
		$db = null; //db切断

		$dat['othermode'] = 'edit'; //編集モード
		echo $blade->run(OTHERFILE,$dat);
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

//編集モードくん本体
function editexec(){
	global $badip;
	global $req_method;
	global $blade,$dat;

	//CSRFトークンをチェック
	if(CHECK_CSRF_TOKEN){
		check_csrf_token();
	}

	$resedit = trim(filter_input(INPUT_POST, 'resedit'));
	$e_no = trim(filter_input(INPUT_POST, 'e_no'));

	if($req_method !== "POST") {error(MSG006);}

	$sub = filter_input(INPUT_POST, 'sub');
	$name = filter_input(INPUT_POST, 'name');
	$mail = filter_input(INPUT_POST, 'mail');
	$url = filter_input(INPUT_POST, 'url');
	$com = filter_input(INPUT_POST, 'com');
	$parent = trim(filter_input(INPUT_POST, 'parent'));
	$picfile = trim(filter_input(INPUT_POST, 'picfile'));
	$invz = trim(filter_input(INPUT_POST, 'invz'));
	$img_w = trim(filter_input(INPUT_POST, 'img_w',FILTER_VALIDATE_INT));
	$img_h = trim(filter_input(INPUT_POST, 'img_h',FILTER_VALIDATE_INT));
	$time = trim(filter_input(INPUT_POST, 'time',FILTER_VALIDATE_INT));
	$pwd = trim(filter_input(INPUT_POST, 'pwd'));
	$pwdh = password_hash($pwd,PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid',FILTER_VALIDATE_INT));

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

	if($resedit == 1) {
		$edittable = 'tabletree';
		$eid = 'iid';
	} else {
		$edittable = 'tablelog';
		$eid = 'tid';
	}

	// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
	$name = str_replace("'","''",$name);
	$sub = str_replace("'","''",$sub);
	$com = str_replace("'","''",$com);
	$mail = str_replace("'","''",$mail);
	$url = str_replace("'","''",$url);
	$host = str_replace("'","''",$host);

	try {
		$db = new PDO(DB_PDO);
		$sql = "UPDATE $edittable set modified = datetime('now', 'localtime'), name = '$name', mail = '$mail', sub = '$sub', com = '$com', url = '$url', host = '$host', exid = '$exid', pwd = '$pwdh' where $eid = '$e_no'";
		$db = $db->exec($sql);
		$db = null;
		$dat['message'] = '編集完了しました。';
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
	unset($name,$mail,$sub,$com,$url,$pwd,$pwdh,$resto,$pictmp,$picfile,$mode);
	//header('Location:'.PHP_SELF);
	ok('編集に成功しました。画面を切り替えます。');
}

//管理モードin
function admin_in() {
	global $blade,$dat;
	$dat['othermode'] = 'admin_in';

	echo $blade->run(OTHERFILE,$dat);
}

//管理モード
function admin() {
	global $admin_pass;
	global $blade,$dat;

	$dat['path'] = IMG_DIR;

	//最大何ページあるのか
	//記事呼び出しから
	try {
		$db = new PDO(DB_PDO);
		//読み込み
		$adminpass = filter_input(INPUT_POST, 'adminpass');
		if ($adminpass === $admin_pass) {
			$sql = "SELECT * FROM tablelog ORDER BY age DESC,tree DESC";
			$oya = array();
			$posts = $db->query($sql);
			while ($bbsline = $posts->fetch() ) {
				if(empty($bbsline)){break;} //スレがなくなったら抜ける
				//$oid = $bbsline["tid"]; //スレのtid(親番号)を取得
				$bbsline['com'] = htmlentities($bbsline['com'],ENT_QUOTES | ENT_HTML5);
				$oya[] = $bbsline;
			}
			$dat['oya'] = $oya;

			//スレッドの記事を取得
			$sqli = "SELECT * FROM tabletree ORDER BY tree DESC";
			$ko = array();
			$postsi = $db->query($sqli);
			while ($res = $postsi->fetch()){
				$res['com'] = htmlentities($res['com'],ENT_QUOTES | ENT_HTML5);
				$ko[] = $res;
			}
			$dat['ko'] = $ko;
			echo $blade->run(ADMINFILE,$dat);
		} else {
			$db = null; //db切断
			error('管理パスを入力してください');
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

// コンティニュー認証 (画像)
function usrchk(){
	$no = filter_input(INPUT_POST, 'no',FILTER_VALIDATE_INT);
	$pwdf = filter_input(INPUT_POST, 'pwd');
	$flag = FALSE;
	try {
		$db = new PDO(DB_PDO);
		//パスワードを取り出す
		$sql ="SELECT pwd FROM tablelog WHERE tid = $no";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg = $msgs->fetch();
		if(password_verify($pwdf,$msg['pwd'])){
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
	global $blade,$dat;
	$dat['okmes'] = $mes;
	$dat['othermode'] = 'ok';
	echo $blade->run(OTHERFILE,$dat);
}

//エラー画面
function error($mes) {
	global $db;
	global $blade,$dat;
	$db = null; //db切断
	$dat['errmes'] = $mes;
	$dat['othermode'] = 'err';
	echo $blade->run(OTHERFILE,$dat);
	exit;
}

//画像差し替え失敗
function error2() {
	global $db;
	global $blade,$dat;
	$db = null; //db切断
	$dat['othermode'] = 'err2';
	echo $blade->run(OTHERFILE,$dat);
	exit;
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

//念のため画像タイプチェック
function getImgType ($img_type, $dest) {
	switch ($img_type) {
		case "image/gif" : return ".gif";
		case "image/jpeg" : return ".jpg";
		case "image/png" : return ".png";
		case "image/webp" : return ".webp";
		default : return error(MSG004, $dest);
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
/**
 * ファイルがあれば削除
 * @param $path
 * @return bool
 */
function safe_unlink ($path) {
	if ($path && is_file($path)) {
		return unlink($path);
	}
	return false;
}

//ログの行数が最大値を超えていたら削除
function logdel() {
	//オーバーした行の画像とスレ番号を取得
	try {
		$db = new PDO(DB_PDO);
		$sqlimg = "SELECT * FROM tablelog ORDER BY tid LIMIT 1";
		$msgs = $db->prepare($sqlimg);
		$msgs->execute();
		$msg = $msgs->fetch();

		$dtid = (int)$msg["tid"]; //消す行のスレ番号
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
			if (is_file(IMG_DIR.$msgdat.'.chi')) {
				unlink(IMG_DIR.$msgdat.'.chi');
			}
		}

		//レスあれば削除
		//カウント
		$sqlc = "SELECT COUNT(*) as cnti FROM tablelog WHERE tid = $dtid";
		$countres = $db->query("$sqlc");
		$countres = $countres->fetch();
		$logcount = $countres["cnti"];
		//削除
		if($logcount !== 0) {
			$delres = "DELETE FROM tablelog WHERE tid = $dtid";
			$db->exec($delres);
		}
		//スレ削除
		$delths = "DELETE FROM tabletree WHERE tid = $dtid";
		$db->exec($delths);

		$sqlimg = null;
		$delths = null;
		$msg = null;
		$dtid = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" .$e->getMessage();
	}
}

/* オートリンク */
function auto_link($proto){
	if(!(stripos($proto,"script")!==false)){//scriptがなければ続行
		$pattern = "{(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)}";
		$replace = "<a href=\"\\1\\2\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">\\1\\2</a>";
		$proto = preg_replace( $pattern, $replace, $proto);
		return $proto;
	}else{
		return $proto;
	}
}

/* ハッシュタグリンク */
function hashtag_link($hashtag) {
	$self = PHP_SELF;
	$pattern = "/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u";
	$replace = " <a href=\"{$self}?mode=search&amp;tag=tag&amp;search=\\1\">#\\1</a>";
	$hashtag = preg_replace( $pattern, $replace, $hashtag);
	return $hashtag;
}

/* '>'色設定 */
function quote($quote) {
	$quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1".RE_START."\\2".RE_END, $quote);
	return $quote;
}

/* 改行を<br>に */
function tobr($com) {
	if (TH_XHTML !== 1) {
		$com = nl2br($com, false);
	} else {
		$com = nl2br($com);
	}
	return $com;
}

/* ID生成 */
function gen_id($userip, $time) {
	if (ID_CYCLE === '0') {
		return substr(crypt(md5($userip.ID_SEED),'id'),-8);
	} elseif (ID_CYCLE === '1') {
		return substr(crypt(md5($userip.ID_SEED.date("Ymd", $time)),'id'),-8);
	} elseif (ID_CYCLE === '2') {
		$week = ceil(date("d", $time) / 7);
		return substr(crypt(md5($userip.ID_SEED.date("Ym", $time).$week),'id'),-8);
	} elseif (ID_CYCLE === '3') {
		return substr(crypt(md5($userip.ID_SEED.date("Ym", $time)),'id'),-8);
	} elseif (ID_CYCLE === '4') {
		return substr(crypt(md5($userip.ID_SEED.date("Y", $time)),'id'),-8);
	} else {
		return substr(crypt(md5($userip.ID_SEED),'id'),-8);
	}
}
