# ROIS

ROISは全く新しいお絵かき掲示板スクリプトです。  
Rapid Oekaki Image System で「ROIS」です。

[PaintBBS NEO](https://github.com/funige/neo/)

## 概要

POTI-board改で使用しているテンプレートエンジン「htmltemplate.inc」は老朽化して今後が危ない…  
ということでなんか新しいテンプレートエンジンはないか探したところ、

[Skinny](http://skinny.sx68.net/) → [POTI-board EVO](https://github.com/satopian/POTI-board_EVO)  
↓  
[smarty](https://www.smarty.net/) → [noe](https://github.com/sakots/noe-board)  
↓  
重いからTwigにしたい…（今後）  
↓  
BladeのほうがTwigより速いらしい！？

という経緯です。

## 設置方法

- パスワードその他を設定  
- アップロード
- OK！ index.phpにアクセスしてください。

## 注意

- まだいわゆるアルファ版です。テーマ等仕様がころころ変わる可能性があります。
- noeとデータベースの互換性はありますが、テーマの互換性はありません。

## サンプルとサポート

[このお絵かき掲示板はSQLiteとさらにBladeを（以下略](https://dev.oekakibbs.net/bbs/rois/)

## 履歴

### [2021/06/23] themes

- NEOでアプレットフィットONのときにキャンバスサイズを拡大して、そのままOFFにすると表示が崩れるの修正。
- シェアボタンあたりとアイコン周り修正。

### [2021/06/14] v0.2.1

- テキストエリアからCtrl+Enterで送信できるようにした。
- 送信ボタンの位置修正。
- アプレットフィットの著作権を表示。

### [2021/06/13] v0.2.0

- パレットファイル(`palette.txt`)の外部化
- アプレットフィット機能実装

### [2021/06/13] theme

- ついったでシェアできないの修正。

### [2021/06/13] v0.1.2

- 動画アニメーションモードに入れないバグ修正。
- 管理モードに入れないバグ修正。

### [2021/06/05] v0.1.1

- 変数のtypo修正。

### [2021/06/05] themes

- `$addinfo`でタグが使えないの修正。

### [2021/06/05] v0.1.0

- 公開。
