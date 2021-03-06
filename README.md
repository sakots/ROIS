# ROIS

## 重要

開発は終了して、[noReita](https://github.com/sakots/noReita) に移行しました。

ROISは全く新しいお絵かき掲示板スクリプトです。
Rapid Oekaki Image System で「ROIS」です。

[PaintBBS NEO](https://github.com/funige/neo/)

![php](https://img.shields.io/badge/php->5.6-green.svg)
![php](https://img.shields.io/badge/php-7.x-green.svg)
![php](https://img.shields.io/badge/php-8.0-green.svg)
![Last commit](https://img.shields.io/github/last-commit/sakots/ROIS)
![version](https://img.shields.io/github/v/release/sakots/ROIS)
![Downloads](https://img.shields.io/github/downloads/sakots/ROIS/total)
![Licence](https://img.shields.io/github/license/sakots/ROIS)

## 概要

POTI-board改で使用しているテンプレートエンジン「htmltemplate.inc」は老朽化して今後が危ない…
ということでなんか新しいテンプレートエンジンはないか探したところ、

[Skinny](http://skinny.sx68.net/) -> [POTI-board EVO](https://github.com/satopian/poti-kaini)

↓

[smarty](https://www.smarty.net/) -> [noe](https://github.com/sakots/noe-board)

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
- noeとデータベースの互換性はありますが、テーマの互換性はありません。-> データベースの互換性もなくなりました。

## サンプルとサポート

[このお絵かき掲示板はSQLiteとさらにBladeを（以下略](https://dev.oekakibbs.net/bbs/rois/)

## 同梱のパレットについて

`p_PCCS.txt`(PCCS:日本色研配色体系パレット)は、[色彩とイメージの情報サイト IROUE](https://tee-room.info/color/database.html) を参考に、`p_munsellHVC.txt`(マンセルHV/Cパレット)は、[マンセル表色系とRGB値](http://k-ichikawa.blog.enjoy.jp/etc/HP/js/Munsell/MSL2RGB0.html) を参照して作成いたしました。

再配布等自由にしていただいて構いません。ただの文字列なので著作権の主張はしませんが、書くのにそれなりの苦労はしましたので、再配布の際はどこかに私の名前を書いていただければと思います。

## 履歴

### [2022/01/03] theme neex

- 画像の後ろの色を調整

### [2021/12/27] v1.2.1

- theme
  - スマホ時画像が大きいときにはみ出ることがあるのを修正

### [2021/11/24] v1.2.0

- `picpost.php`、`save.php`にCRSF対策

### [2021/10/30] v1.1.0

- URL欄に設定を無視して文字列を書き込める深刻なバグ修正
- BladeOneの動作環境にあわせて、対応PHPのバージョンを5.6.0以上に修正

### [2021/10/25] v1.0.0.1

- 同梱のパレットデータ(p_munsellHVC)の不具合修正
- テーマneexのcssのミスを正式に修正

### [2021/10/24] theme neex

- スタイルシートの指定が間違っていたので修正
  - （1.0.0の配布ファイルを差し替えます）

### [2021/10/24] v1.0.0

- データベース名を変更できるようにした
  - configに変更があるので注意
- IDの変更スパンを指定できるようにした
  - 変更なし、1日、1週間、1か月、1年
- レスの場合にも描画時間を取得しようとしてエラーを出していたのを修正
- neex、monoredテーマのcookieまわりが全体的におかしかったので修正
- monoredのscssファイルを新しい書式に修正
- BladeOneのバージョンを4.1にアップデート

### [2021/10/04] v0.99.16.b

- 配布同梱のNEOを更新(v1.5.12 -> v1.5.14)
- テーマneexの不具合修正

### [2021/09/18] v0.99.16

- 続きから描くと描画時間がリセットされるバグ修正
- 新テーマ「neex」実装
- 「そうだね」をテーマ設定で変更可能にした

### [2021/09/13] v0.99.15

- 連続する改行の調整完了

### [2021/09/11] v0.99.15b

- 改行がおかしいのが修正できない

### [2021/09/11] v0.99.14

- 投稿時間等をユーザーコードから取得するようにして、いたずら対策した
- 引用の範囲が本文全部になるの修正

### [2021/08/30] v0.99.13

- ROISの`picpost.php`と`save.php`をPOTIで使っても大丈夫にした
- 続きから描くときのバグ修正
- `config.php`に日付フォーマットの設定追加(なくても大丈夫です)

### [2021/08/30] v0.99.12

- レス画面のコメントの並びが逆順になっていたの修正
- レスが編集できなかったの修正
- 検索がおかしかったの修正
- オートリンクもおかしかったので修正
- 画像描画時間をユーザーコードから算出するようにした
- ついでに使用ツールもユーザーコードを拡張して記録するようにした

### [2021/08/29] v0.99.11

- レス画面でオートリンクとハッシュタグリンクが機能していないの修正
- 引用レスで色が変わるのが機能していなかったの修正
- 絵を描くのに使ったツールを頑張って記録するようにした
  - データベースを拡大しています。これ以上形式が変わらないようにせっかくなので4項目予備として増やしておきました。

### [2021/08/29] v0.99.10

- レス順が逆になっていたの修正
- ハッシュタグリンク、URLオートリンクが機能していなかったの修正
- 検索窓のSQL脆弱性を修正
- themes
  - カタログモードの記述ミス修正

### [2021/08/28] v0.99.9

- configでの最大スレッド数を超えたときの処理ができるように修正
- テーマの画像表示サイズ修正

### [2021/08/21] v0.99.8

- configでの最大スレッド数を超えたときの処理が、ファイルの削除しかできないので諦めた。
- それに伴いスレが消えるときにレスも全部消えるようにしたので、configでの最大レス数設定が無意味に
- 各テーマの「書式がXHTMLのものか」の定義が消えていたの修正
- ChikenPaintがちょっと使いやすくなった。
- パレット切り替えのクッキーの処理が正しく行われていないのをこんどこそ修正

### [2021/08/19] v0.99.7b

- themes
  - パレット切り替えのクッキーの処理が正しく行われていないの修正

### [2021/08/19] v0.99.7

- ChickenPaintを最新ビルドに更新
- 2021/08/15のテーマを同梱

### [2021/08/15] themes

- 絵を描くのに集中できるよう、NEOのキャンバス周りの彩度を下げた
- ついでに配色パターンまた追加

### [2021/08/15] v0.99.6

- palette選択をCookieに保存できるようにした
- コード整理

### [2021/08/15] v0.99.5

- 続きから描けないほかデータベース周りのエラー修正
- Cookie周り修正

### [2021/08/15] v0.99.4

- 検索でエラーが出るようになっていたの修正

### [2021/08/15] v0.99.3

- ChickenPaintのレイヤー情報ファイルがあると、動画へのリンクが出るのを修正
- パレットのファイルの変数が未定義だったのを修正
- ゆるやかな比較で true と比較する意味が無い箇所を整理
- 続きから描くとき、レイヤー情報がある場合画像からでもそのツールで続きから描くように変更
- Cookie関連のバグ修正
- データベース関連の脆弱性対応

### [2021/08/13] v0.99.2

- 続きから描いた画像のファイル名と動画名を新しく変更(キャッシュが表示される、キャッシュの続きから描くことがあるため)
- 続きから描いた時の描画時間がおかしくなるの修正
- ChickenPaint関連の不具合修正
- 各themeのcssにスレッドタイトルの文字色設定追加

### [2021/08/12] theme

- カタログモード等で追加のお知らせがうまく表示されていないの修正
- monoredにSQL色追加
- SVGアイコンの読み込み方法及びアイコン微修正

### [2021/08/12] v0.99.1

- phpスクリプト内の、Bladeに渡す配列の書式変更(by さとぴあ)
- ユーザーコードに関するバグ修正(by さとぴあ)
- しぃペインターで動画から続きを書けないミス修正

### [2021/08/11] v0.99.0

- 続きから描くの画像差し替えが機能していなかったの修正
- パレット選択機能実装
- そのついでにパレットデータ`p_PCCS.txt`,`p_munsellHVC.txt`を作ったので同梱
- configに設定はあったトラブルシューティングを実装
- 各テーマ更新
- あとなんかたくさん更新した気がするけど忘れた

### [2021/08/10] v0.4.2b1

- いったんコミット
- 続きから描くときとアニメ再生時におかしかったの修正

### [2021/08/09] v0.4.1

- 各テーマ
  - 追加お知らせの表示がおかしいの修正
  - chickenpaintで描画時間が記録されないバグ修正
  - シェアボタンのリンクがうまく作成されないの修正

### [2021/08/09] v0.4.0

- [chickenpaint](https://github.com/thenickdude/chickenpaint)でのお絵描きに対応
- `config.php`
  - `$addinfo`を配列とし、htmlタグ`<li> </li>`で囲まれるように変更
  - 管理パスが初期値のままではスクリプトが動かないように念のため変更
  - 再設定をお願いします。
- 07/30の「データベースのサイズをあとから変えるのが困難」が解決できそうなのでこのまま行くことにした。
- 各テーマもかなり更新しております

### [2021/08/02] v0.3.0

- セキュリティ対策の改善
  - タグや特殊文字は表示するときに無効化する。
  - さとぴあさんありがとうございます。
- 改行はコードのまま保存し、表示のときにHTMLタグにするように変更
- コンフィグファイルの互換性がなくなったときは起動できないようにした
- csrfトークンを使って不正な投稿を拒絶する設定追加

### [2021/07/30] 今後

~~ - やはり「データベースのサイズをあとから変えるのが困難」ということで、ログの保存形式をjsonかなにかにしようと思った。 ~~

### [2021/07/13] v0.2.4

- v0.2.3がちょっと間違ってたので修正。

### [2021/07/12] v0.2.3

- URL欄にURLじゃないものが入っていた場合表示しないように修正。

### [2021/07/06] v0.2.2

- theme(MONORED)
  - css切り替え機能の脆弱性その他修正
  - そしたらjqueryが不要になったので削除
  - Paint画面に無意味にカラーピッカーを搭載

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
