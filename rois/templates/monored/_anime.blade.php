<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8">
		<title>{{$btitle}}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_red.min.css">
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_main.min.css" id="css1" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_dark.min.css" id="css2" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_deep.min.css" id="css3" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_mayo.min.css" id="css4" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_dev.min.css" id="css5" disabled>
		<link rel="stylesheet" href="templates/{{$themedir}}/css/mono_dql.min.css" id="css6" disabled>
		<script src="templates/{{$themedir}}/switchcss.js"></script>
		@if ($tool == 'neo')
		<link rel="stylesheet" href="neo.css?{{$a_stime}}" type="text/css">
		<script src="neo.js?{{$a_stime}}" charset="utf-8"></script>
		@else
		<!-- Javaが使えるかどうか判定 使えなければcheerpJをロード -->
		<script>
			function cheerpJLoad() {
			var jEnabled = navigator.javaEnabled();
			if(!jEnabled){
				var sN = document.createElement("script");
				sN.src = "https://cjrtnc.leaningtech.com/2.2/loader.js";
				var s0 = document.getElementsByTagName("script")[0];
				s0.parentNode.insertBefore(sN, s0);
				sN.addEventListener("load", function(){ cheerpjInit(); }, false);
				}
			}
			window.addEventListener("load", function() { cheerpJLoad(); }, false);
		</script>
		@endif
		<script src="loadcookie.js"></script>
	</head>
	<body id="paintmode">
		<header>
			<h1><a href="{{$self}}">{{$btitle}}</a></h1>
			<div>
				<a href="{{$home}}" target="_top">[ホーム]</a>
				<a href="{{$self}}?mode=admin_in">[管理モード]</a>
			</div>
			<hr>
			<section>
				<p class="top menu">
					<a href="{{$self}}">[トップ]</a>
				</p>
			</section>
			<hr>
			<h2 class="oekaki">PCH MODE</h2>
			<hr>
		</header>
		<main>
			<section id="appstage">
				<div class="app">
					@if ($tool == 'neo')
					<applet-dummy name="pch" code="pch.PCHViewer.class" archive="PCHViewer.jar,PaintBBS.jar" width="{{$w}}" height="{{$h}}" mayscript>
					@else
					<applet name="pch" code="pch2.PCHViewer.class" archive="PCHViewer.jar,spainter_all.jar" codebase="./"  width="{{$w}}" height="{{$h}}">
						<param name="res.zip" value="res.zip">
						<!--(しぃペインターv1.05_9以前を使うなら res_normal.zip に変更)-->
						<param name="tt.zip" value="tt_def.zip">
						<param name="tt_size" value="31">
					@endif
						<param name="image_width" value="{{$picw}}">
						<param name="image_height" value="{{$pich}}">
						<param name="pch_file" value="{{$path}}{{$pchfile}}">
						<param name="speed" value="{{$speed}}">
						<param name="buffer_progress" value="false">
						<param name="buffer_canvas" value="false">
					@if ($tool == 'neo')
					</applet-dummy>
					@else
					</applet>
					@endif
				</div>
			</section>
			<section class="thread">
				<hr>
				<p>
					<a href="{{$path}}{{$pchfile}}" target="_blank">Download</a>
					@if (isset($datasize))
						- Datasize {{$datasize}} B
					@endif
				</p>
				<hr>
			</section>
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
