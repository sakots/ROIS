<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8">
		<title>{{$btitle}}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="templates/{{$themedir}}/css/nanj/neex.min.css">
		<link rel="stylesheet" href="templates/{{$themedir}}/css/dark/neex.min.css" id="css1" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/fine/neex.min.css" id="css2" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/deep/neex.min.css" id="css3" disabled>
		<script src="templates/{{$themedir}}/switchcss.js"></script>
	</head>
	<body>
	<header id="header">
			<div class="titlebox">
				<h1><a href="{{$self}}">{{$btitle}}</a></h1>
				<div>
					<section>
						<p class="top menu">
							<a href="{{$self}}?mode=catalog">[カタログ]</a>
							<a href="{{$self}}?mode=pictmp">[投稿途中の絵]</a>
							<a href="#footer">[↓]</a>
						</p>
					</section>
					<hr>
					<div>
						<section class="epost">
							<form action="{{$self}}" method="post" enctype="multipart/form-data">
								<p>
									<label>幅：<input class="form" type="number" min="{{$pdefw}}" name="picw" value="{{$pdefw}}"></label>
									<label>高さ：<input class="form" type="number" min="{{$pdefh}}" name="pich" value="{{$pdefh}}"></label>
									<input class="button" type="submit" value="お絵かき"><br>
									<input type="hidden" name="mode" value="paint">
									<label for="tools">ツール</label>
									<select name="tools" id="tools">
										<option value="neo">PaintBBS NEO</option>
										@if ($use_shi_p)<option value="shi">しぃペインター</option> @endif
										@if ($use_chicken)<option value="chicken">ChickenPaint</option> @endif
									</select><br>
									<label for="palettes">パレット</label>
									@if ($select_palettes)
									<select name="palettes" id="palettes">
										@foreach ($pallets_dat as $palette)
										<option value="{{$pallets_dat[$loop->index][1]}}" id="{{$loop->index}}">{{$pallets_dat[$loop->index][0]}}</option>
										@endforeach
									</select>
									@else
									<select name="palettes" id="palettes">
										<option value="neo" id="0">標準</option>
									</select>
									@endif
									@if ($useanime)
									<label><input type="checkbox" value="true" name="anime" title="動画記録"@if ($defanime) checked @endif>描画記録</label>
									@endif
								</p>
							</form>
							<ul>
								<li>iPadやスマートフォンでも描けるお絵かき掲示板です。お絵かきできるサイズは幅300～{{$pmaxw}}px、高さ300～{{$pmaxh}}pxです。</li>
								@foreach ($addinfo as $info) @if (!empty($info[$loop->index]))
									<li>{!! $addinfo[$loop->index] !!}</li>
								@endif @endforeach
							</ul>
						</section>
						<hr>
						<section class="paging">
							<p>
								@if ($back === 0)
									<span class="se">[START]</span>
								@else
									<span class="se">&lt;&lt;<a href="{{$self}}?page={{$back}}">[BACK]</a></span>
								@endif
								@foreach ($paging as $pp)
									@if ($pp['p'] == $nowpage)
										<em class="thispage">[{{$pp['p']}}]</em>
									@else
										<a href="{{$self}}?page={{$pp['p']}}">[{{$pp['p']}}]</a>
									@endif
								@endforeach
								@if ($next == ($max_page + 1))
									<span class="se">[END]</span>
								@else
									<span class="se"><a href="{{$self}}?page={{$next}}">[NEXT]</a>&gt;&gt;</span>
								@endif
							</p>
						</section>
					</div>
				</div>
				<hr>
				<div class="search_box">
					<p>作者名/本文(ハッシュタグ)検索</p>
					<form class="search" method="GET" action="{{$self}}">
						<input type="hidden" name="mode" value="search">
						<label><input type="radio" name="bubun" value="bubun">部分一致</label>
						<label><input type="radio" name="bubun" value="kanzen">完全一致</label>
						<label><input type="radio" name="tag" value="tag">本文(ハッシュタグ)</label>
						<br>
						<input type="text" name="search" placeholder="検索" size="20">
						<input type="submit" value=" 検索 ">
					</form>
				</div>
				<hr>
				<form class="delf" action="{{$self}}" method="post">
					<p>
						削除/編集フォーム<br>
						<select name="delt">
							<option value="1">レス</option>
							<option value="0">親</option>
						</select>
						No <input class="form" type="number" min="1" name="delno" value="" autocomplete="off">
						Pass <input class="form" type="password" name="pwd" value="" autocomplete="current-password">
						<select class="form" name="mode">
							<option value="edit">編集</option>
							<option value="del">削除</option>
						</select>
						<input class="button" type="submit" value=" OK ">
						<hr>
						<label for="mystyle">Color</label>
						<span class="stylechanger">
							<select class="form" name="select" id="mystyle" onchange="SetCss(this);">
								<option value="nanj/neex.min.css">nanj</option>
								<option value="dark/neex.min.css">dark</option>
								<option value="fine/neex.min.css">fine</option>
								<option value="deep/neex.min.css">deep</option>
							</select>
						</span>
					</p>
				</form>
				<script>
					colorIdx = GetCookie('colorIdx');
					document.getElementById("mystyle").selectedIndex = colorIdx;
				</script>
			</div>
			<div>
				<p class="sysmsg">{{$message}}</p>
				<a href="{{$home}}" target="_top">[ホーム]</a>
				<a href="{{$self}}?mode=admin_in">[管理モード]</a>
			</div>
		</header>
		<main>
			<div class="thread" id="catalog">
				@if (!empty($oya))
					@foreach ($oya as $bbsline)
					<div>
						<div>
							@if ($bbsline['picfile'] == true)
							<p>
								<a href="{{$self}}?mode=res&amp;res={{$bbsline['tid']}}" title="{{$bbsline['sub']}} (by {{$bbsline['name']}})"><img src="{{$path}}{{$bbsline['picfile']}}" alt="{{$bbsline['sub']}} (by {{$bbsline['name']}})" loading="lazy"></a>
							</p>
							@else
							<p>
								<a href="{{$self}}?mode=res&amp;res={{$bbsline['tid']}}" title="{{$bbsline['sub']}} (by {{$bbsline['name']}})">{{$bbsline['sub']}} (by {{$bbsline['name']}})</a>
							</p>
							@endif
							<p>
								[{{$bbsline['tid']}}]
							</p>
						</div>
					</div>
					@endforeach
				@endif
				@if ($catalogmode == 'hashsearch')
					@if (!empty($ko))
						@foreach ($ko as $res)
							<div>
								<div>
									@if ($res['picfile'] == true)
										<p>
											<a href="{{$self}}?mode=res&amp;res={{$res['tid']}}" title="{{$res['sub']}} (by {{$res['name']}})"><img src="{{$path}}{{$res['picfile']}}" alt="{{$res['sub']}} (by {{$res['name']}})"></a>
										</p>
									@else
										<p>
											<a href="{{$self}}?mode=res&amp;res={{$res['tid']}}" title="{{$res['sub']}} (by {{$res['name']}})">{!! mb_substr($res['com'], 0, 30)!!}</a>
										</p>
									@endif
									<p>
										[{{$res['tid']}}]({{$res['iid']}})
									</p>
								</div>
							</div>
						@endforeach
					@endif
				@endif
			</div>
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
					<a href="https://github.com/funige/neo/" target="_top" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>
					@if ($use_shi_p) ,<a href="http://hp.vector.co.jp/authors/VA016309/" target="_top" rel="noopener noreferrer" title="by しぃちゃん">Shi-Painter</a> @endif
					@if ($use_chicken) ,<a href="https://github.com/thenickdude/chickenpaint" target="_blank" rel="nofollow noopener noreferrer" title="by Nicholas Sherlock">ChickenPaint</a> @endif
				</p>
				<p>
					UseFunction -
					<!-- http://wondercatstudio.com/ -->DynamicPalette,
					<a href="https://huruihone.tumblr.com/" target="_top" rel="noopener noreferrer" title="by Soto">AppletFit</a>,
					<a href="https://github.com/imgix/luminous" target="_top" rel="noopener noreferrer" title="by imgix">Luminous</a>,
					<a href="https://github.com/EFTEC/BladeOne" target="_top" rel="noopener noreferrer" title="by EFTEC">BladeOne</a>
				</p>
			</div>
		</footer>
	</body>
</html>