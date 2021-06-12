<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8">
		<title>{{$btitle}}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script>
			var css = GetCookie("CSS");
			if(css == ""){css = "mono_red.min.css";}
			document.write('<link rel="stylesheet" href="./templates/{{$themedir}}/css/' + css + '" type="text/css">');
			function SetCss(obj){
				var idx = obj.selectedIndex;
				file = obj.options[idx].value;
				SetCookie("CSS", file);
				window.location.reload();
			}
			function GetCookie(key){
				var tmp = document.cookie + ";";
				var tmp1 = tmp.indexOf(key, 0);
				if(tmp1 != -1){
					tmp = tmp.substring(tmp1, tmp.length);
					var start = tmp.indexOf("=", 0) + 1;
					var end = tmp.indexOf(";", start);
					return(unescape(tmp.substring(start,end)));
					}
				return("");
			}
			function SetCookie(key, val){
				document.cookie = key + "=" + escape(val) + ";max-age=31536000;";
			}
		</script>
		<noscript>
			<link rel="stylesheet" href="./templates/{{$themedir}}/css/mono_red.min.css" type="text/css">
		</noscript>
		@if ($useneo == true)
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
					@if ($useneo == true)
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
					@if ($useneo == true)
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
					<a href="https://github.com/funige/neo/" target="_top" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>,
					<a href="http://hp.vector.co.jp/authors/VA016309/" target="_top" rel="noopener noreferrer" title="by しぃちゃん">Shi-Painter</a>
				</p>
				<p>
					UseFunction -
					<!-- http://wondercatstudio.com/ -->DynamicPalette,
					<a href="https://github.com/EFTEC/BladeOne" target="_top" rel="noopener noreferrer" title="by EFTEC">BladeOne</a>
				</p>
			</div>
		</footer>
	</body>
</html>
