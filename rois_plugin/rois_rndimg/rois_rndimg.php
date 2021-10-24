<?php
// ROISの画像をランダムにHTMLファイルに呼び出すphp
// rois_rndimg.php(c)さこつ 2021 lot.210613.0
// https://sakots.red/ , https://dev.oekakibbs.net/
// さとぴあさんの https://github.com/satopian/potiboard_plugin
// を参考に作りました。
// フリーウェアですが著作権は放棄しません。

// 使い方
//ROISのindex.phpと同じディレクトリにアップロードして
//HTMLファイルに画像を表示する時のように
//rois_rndimg.php ←このファイルの名前をurlで指定します。

//例）
// <img src="https://hoge.ne.jp/bbs/rois_rndimg.php" alt="" width="300">
//↑
//この例では横幅300px、高さの指定なし。

//---------------- 設定 ----------------

//画像がない時に表示する画像を指定
$default = '';
//例
// $default='https://hoge.ne.jp/image.png';
//設定しないなら初期値の
// $default='';
//で。

//--------- 説明と設定ここまで ---------

include(__DIR__.'/config.php');//config.phpの設定を読み込む

//データベース接続PDO
define('DB_PDO', 'sqlite:'.DB_NAME.'.db');


//db接続の前にdbがなかったらそもそも処理しない
//これを入れないとテーブルも何もないdbが作られていろいろ困る
if (!is_file(DB_NAME.'.db')) {
    $filename = $default;
} else {
    try {
        //db接続
        $db = new PDO(DB_PDO);
        //LIMIT 1 で取り出す画像が1枚だけ決まる。
        //紆余曲折を経てこの文に行き着いた →
        //https://www.it-swarm-ja.com/ja/sql/SQLiteでランダムな行を選択します/970867568/
        $sql ="SELECT picfile FROM tablelog LIMIT 1 OFFSET abs(random() % (SELECT count(*) FROM tablelog));";
        $msgs = $db->prepare($sql);
        $msgs->execute();
        $msg = $msgs->fetch(); //取り出せた
        //配列$msg内のpicfileに格納されている
        //$msgがからっぽ=ログに画像がない場合はデフォルト画像
        if (empty($msg)) {
            $filename = $default;
        } else {
            $filename = IMG_DIR.$msg["picfile"];
        }
        $db = null;// db切断
    } catch (PDOException $e) {
        echo "DB接続エラー:" .$e->getMessage();
    }
}

//画像を出力

$img_type=mime_content_type($filename);

switch ($img_type):
	case 'image/png':
		header('Content-Type: image/png');
		break;
	case 'image/jpeg':
		header('Content-Type: image/jpeg');
		break;
	case 'image/gif':
		header('Content-Type: image/gif');
		break;
	default :
		header('Content-Type: image/png');
	endswitch;
readfile($filename);
