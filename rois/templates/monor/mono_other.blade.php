<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="./templates/{{$themedir}}/css/mono_main.css" type="text/css">
		<title>{{$btitle}}</title>
		{{-- ok画面専用ヘッダ --}}
		@if ($othermode == 'ok')
		<meta http-equiv="refresh" content="1; URL={{$self}}">
		@endif
	</head>
	<body>
		<header>
			<h1><a href="{{$self}}">{{$btitle}}</a></h1>
			<div>
				<a href="{{$home}}" target="_top">[ホーム]</a>
				<a href="{{$self}}?mode=admin_in">[管理モード]</a>
			</div>
			<hr>
			<section>
				<p class="menu">
					<a href="{{$self}}">[通常モード]</a>
				</p>
			</section>
			<section>
				<p class="sysmsg">{{$message}}</p>
			</section>
			<hr>
		</header>
		<main>
            {{-- 記事編集モードスタート --}}
			@if ($othermode == 'edit')
			<section>
				<div class="thread">
					<h1 class="oekaki">投稿フォーム</h1>
                    @foreach ($oya as $bbsline)
					<form class="ppost postform" action="{{$self}}?mode=editexec" method="post" enctype="multipart/form-data">
						<table>
							<tr>
								<td>name</td>
								<td><input type="text" name="name" size="28" autocomplete="username" value="{{$bbsline['name']}}"></td>
							</tr>
							<tr>
								<td>mail</td>
								<td><input type="text" name="mail" size="28" value="" autocomplete="email" value="{{$bbsline['mail']}}"></td>
							</tr>
							<tr>
								<td>URL</td>
								<td><input type="text" name="url" size="28" value="" autocomplete="url" value="{{$bbsline['url']}}"></td>
							</tr>
							<tr>
								<td>subject</td>
								<td>
									<input type="text" name="sub" size="35" autocomplete="section-sub" value="{{$bbsline['sub']}}">
									<input type="submit" name="send" value="書き込む">
									<input type="hidden" name="invz" value="0">
                                    <input type="hidden" name="exid" value="0">
									@if (isset($resedit))
									<input type="hidden" name="resedit" value="1">
									<input type="hidden" name="e_no" value="{{$bbsline['iid']}}">
									@else
									<input type="hidden" name="e_no" value="{{$bbsline['tid']}}">
									@endif
									@if ($token != null)
										<input type="hidden" name="token" value="{{$token}}">
									@else
										<input type="hidden" name="token" value="">
									@endif
								</td>
							</tr>
							<tr>
								<td>comment</td>
								<td><textarea name="com" cols="48" rows="4" wrap="soft">{{$bbsline['com']}}</textarea></td>
							</tr>
							<tr>
								<td>pass</td>
								<td><input type="password" name="pwd" size="8" value="" autocomplete="current-password"></td>
							</tr>
						</table>
                    </form>
                    @endforeach
				</div>
            </section>
			@endif
			{{-- 記事編集モードおわり --}}
			{{-- コンティニューモードin --}}
			@if ($othermode == 'incontinue')
			<section>
				<div class="thread">
					<h1 class="oekaki">続きから描く</h1>
					@foreach ($oya as $bbsline)
					<figure>
						<img src="{{$path}}{{$bbsline['picfile']}}">
						<figcaption>{{$bbsline['picfile']}}
							@if ($dptime == 1)
								@if ($bbsline['time'] != null)
									描画時間：{{$bbsline['time']}}
								@endif
							@endif
						</figcaption>
					</figure>
					<hr class="hr">
					<form action="{{$self}}" method="post" enctype="multipart/form-data">
						<input type="hidden" name="mode" value="contpaint">
						<input type="hidden" name="anime" value="true">
						<input type="hidden" name="picw" value="{{$bbsline['img_w']}}">
						<input type="hidden" name="pich" value="{{$bbsline['img_h']}}">
						<input type="hidden" name="no" value="{{$bbsline['tid']}}">
						<input type="hidden" name="pch" value="{{$bbsline['pchfile']}}">
						<input type="hidden" name="img" value="{{$bbsline['picfile']}}">
						<select class="form" name="ctype">
							@if ($ctype_pch == true)
							<option value="pch">動画から</option>
							@endif
							@if ($ctype_img == true)
							<option value="img">画像から</option>
							@endif
						</select>
						<select class="form" name="type">
							<option value="rep" selected>差し替え</option>
							<option value="new">新規投稿</option>
						</select>
						@if ($passflag == true)
						Pass<input class="form" type="password" name="pwd" size="8" value="">
						@endif
						<input class="button" type="submit" value="続きを描く">
						<!-- NEOを使う -->
						@if ($ctype_pch == false)
						<label><input type="checkbox" name="useneo" checked="checked"><span class="use_neo">USE NEO</span></label>
						@endif
						@if ($useneo == false)
						<input type="hidden" name="shi" value="1">
						@else
						<input type="hidden" name="useneo" value="1">
						@endif
					</form>
					@if ($passflag == true)
					<ul>
						@if ($newpost_nopassword == true)
						<li>新規投稿なら削除キーがなくても続きを描く事ができます。</li>
						@else
						<li>続きを描くには描いたときの削除キーが必要です。</li>
						@endif
						</ul>
					@endif
					@endforeach
				</div>
			</section>
			@endif
			{{-- コンティニューモードin おわり --}}
			{{-- 管理モードin --}}
			@if ($othermode == 'admin_in')
			<section>
				<div class="thread">
					<h1 class="oekaki">管理モードin</h1>
					<hr>
					<form action="{{$self}}?mode=admin" method="post">
						<input type="hidden" name="admin" value="admin">
						<label>ADMIN PASS <input class="form" type="password" name="adminpass" size="8"></label>
						<input class="button" type="submit" value="SUBMIT">
					</form>
					<hr>
					<p>
						<a href="#" onclick="javascript:window.history.back(-1);return false;">[もどる]</a>
					</p>
				</div>
			</section>
			@endif
			{{-- 管理モードin おわり --}}
			{{-- ok画面 --}}
			@if ($othermode == 'ok')
			<section>
				<div class="thread">
					<h1 class="oekaki">OK！</h1>
					<hr>
					<p class="ok">{{$okmes}}</p>
					<p><a href="{{$self}}">[リロード]</a></p>
				</div>
			</section>
			@endif
			{{-- ok画面 おわり --}}
			{{-- エラー画面 --}}
			@if ($othermode == 'err')
			<section>
				<div class="thread">
					<h1 class="oekaki">エラー！！</h1>
					<hr>
					<p class="err">{{$errmes}}</p>
					<p><a href="#" onclick="javascript:window.history.back(-1);return false;">[もどる]</a></p>
				</div>
			</section>
			@endif
			{{-- エラー画面 おわり --}}
			{{-- 画像差し替え失敗専用エラー --}}
			@if ($othermode == 'err2')
			<section>
				<div class="thread">
					<h1 class="oekaki">エラー？</h1>
					<hr>
					<p class="err">画像が見当たりません。</p>
					<p>
						投稿に失敗している可能性があります。<a href="{{$self}}?mode=piccom">アップロード途中の画像</a>に残っているかもしれません。
					</p>
				</div>
			</section>
			@endif
			{{-- 画像差し替え失敗専用エラー おわり --}}
			<script src="loadcookie.js"></script>
			<script>
				l(); //LoadCookie
			</script>
		</main>
		<footer id="footer">
			<div class="copy">
				<!-- 著作権表示 -->
				<p>
					<a href="https://dev.oekakibbs.net/" target="_top">ROIS {{$ver}}</a>
					Web Style by <a href="https://dev.oekakibbs.net/" target="_top" title="{{$tname}} {{$tver}} (by お絵かきBBSラボ)">{{$tname}}</a>
				</p>
				<p>
					OekakiApplet - 
					<a href="https://github.com/funige/neo/" target="_top" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>,
					<a href="http://hp.vector.co.jp/authors/VA016309/" target="_top" rel="noopener noreferrer" title="by しぃちゃん">Shi-Painter</a>
				</p>
				<p>
					UseFunction -
					<!-- http://wondercatstudio.com/ -->DynamicPalette,
					<a href="https://huruihone.tumblr.com/" target="_top" rel="noopener noreferrer" title="by Soto">AppletFit</a>,
					<a href="https://github.com/EFTEC/BladeOne" target="_top" rel="noopener noreferrer" title="by EFTEC">BladeOne</a>
				</p>
			</div>
		</footer>
	</body>
</html>
