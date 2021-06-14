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
	</head>
	<body>
		<header id="header">
			<h1><a href="{{$self}}">{{$btitle}}</a></h1>
			<div>
				<a href="{{$home}}" target="_top">[ホーム]</a>
				<a href="{{$self}}?mode=admin_in">[管理モード]</a>
			</div>
			<hr>
			<div>
				<section>
					<p class="top menu">
						<a href="{{$self}}?mode=catalog">[カタログ]</a>
						<a href="{{$self}}?mode=piccom">[投稿途中の絵]</a>
						<a href="#footer">[↓]</a>
					</p>
				</section>
				<section>
					<p class="sysmsg">{{$message}}</p>
				</section>
			</div>
			<hr>
			<div>
				<section class="epost">
					<form action="{{$self}}" method="post" enctype="multipart/form-data">
						<p>
							<label>幅：<input class="form" type="number" min="{{$pdefw}}" name="picw" value="{{$pdefw}}"></label>
							<label>高さ：<input class="form" type="number" min="{{$pdefh}}" name="pich" value="{{$pdefh}}"></label>
							<input type="hidden" name="mode" value="paint">
							<input class="button" type="submit" value="お絵かき">
							@if ($useanime == 1)<label><input type="checkbox" value="true" name="anime" title="動画記録"@if ($defanime == 1) checked @endif>アニメーション記録</label>@endif
							<label><input type="checkbox" value="true" name="useneo" title="NEOを使う" checked>NEOを使う</label>
						</p>
					</form>
					<ul>
						<li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
						<li>お絵かきできるサイズは幅300～{{$pmaxw}}px、高さ300～{{$pmaxh}}pxです。</li>
						<li>NEOを使うのチェックを外すとしぃペインターが起動します。</li>
						{!!$addinfo!!}
					</ul>
				</section>
				<hr>
				<section class="paging">
					<p>
						@if ($back == 0)
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
		</header>
		<main>
			<div>
				@if (!empty($oya))
					@foreach ($oya as $bbsline)
						<section class="thread">
							<h3 class="oyat">[{{$bbsline['tid']}}] {{$bbsline['sub']}}</h3>
							<section>
								<h4 id=oya>
									<span class="oyaname"><a href="{{$self}}?mode=search&amp;bubun=kanzen&amp;search={{$bbsline['name']}}">{{$bbsline['name']}}</a></span>
									@if ($bbsline['modified'] == $bbsline['created'])
										{{$bbsline['modified']}}
									@else
										{{$bbsline['created']}} {{$updatemark}} {{$bbsline['modified']}}
									@endif
									@if ($bbsline['mail'] == true)
										<span class="mail"><a href="mailto:{{$bbsline['mail']}}">[mail]</a></span>
									@endif
									@if ($bbsline['url'] == true)
										<span class="url"><a href="{{$bbsline['url']}}" target="_blank" rel="nofollow noopener noreferrer">[URL]</a></span>
									@endif
									@if ($dispid == 1)
										<span class="id">ID：{{$bbsline['id']}}</span>
									@endif
									<span class="sodane"><a href="{{$self}}?mode=sodane&amp;resto={{$bbsline['tid']}}">そうだね
									@if ($bbsline['exid'] == 0)
										+
									@else
										x{{$bbsline['exid']}}
									@endif
									</a></span>
								</h4>
								@if ($bbsline['picfile'] == true)
									@if ($dptime == 1)
										@if ($bbsline['time'] != null)
											<h5>描画時間：{{$bbsline['time']}}</h5>
										@endif
									@endif
									<figure>
										<figcaption><a href="{{$path}}{$bbsline['picfile']}" target="_blank" data-title="{{$bbsline['picfile']}}">{{$bbsline['picfile']}}</a>
										@if ($bbsline['pchfile'] != null)
											<a href="{{$self}}?mode=anime&amp;pch={{$bbsline['pchfile']}}" target="_blank">●動画</a>
										@endif
										@if ($use_continue == 1)
											<a href="{{$self}}?mode=continue&amp;no={{$bbsline['picfile']}}">●続きを描く</a>
										@endif
										</figcaption>
										<a href="{{$path}}{{$bbsline['picfile']}}" target="_blank"><img src="{{$path}}{{$bbsline['picfile']}}" alt="{{$bbsline['picfile']}}"></a>
									</figure>
								@endif
								<p class="comment oya">{!!$bbsline['com']!!}</p>
								@if (($m_tid - $bbsline['tid']) > $thid)
								<div class="res">
									<p class="limit">このスレは古いのでもうすぐ消えます。</p>
								</div>
								@endif
								@if ($bbsline['rflag'] == true)
								<div class="res">
									<p class="limit">レス{{$bbsline['res_d_su']}}件省略。すべて見るには返信ボタンを押してください。</p>
								</div>
								@endif
								@if (!empty($ko))
									@foreach ($ko as $res)
										@if ($bbsline['tid'] == $res['tid'])
											@if ($res['resno'] <= $bbsline['res_d_su'])
											@else
												<section class="res">
													<section>
														<h3>[{{$res['iid']}}] {{$res['sub']}}</h3>
														<h4>
															名前：<span class="resname">{{$res['name']}}</span>：
															@if ($res['modified'] == $res['created'])
																{{$res['modified']}}
															@else
																{{$res['created']}} {{$updatemark}} {{$res['modified']}}
															@endif
															@if ($res['mail'] == true)
																<span class="mail"><a href="mailto:{{$res['mail']}}">[mail]</a></span> 
															@endif
															@if ($res['url'] == true)
																<span class="url"><a href="{{$res['url']}}" target="_blank" rel="nofollow noopener noreferrer">[URL]</a></span>
															@endif
															@if ($dispid == 1)
																<span class="id">ID：{{$res['id']}}</span>
															@endif
															<span class="sodane"><a href="{{$self}}?mode=rsodane&amp;resto={{$res['iid']}}">そうだね
															@if ($res['exid'] == 0)
																+
															@else
																x{{$res['exid']}}
															@endif
															</a></span>
														</h4>
														<p class="comment">{!!$res['com']!!}</p>
													</section>
												</section>
											@endif
										@endif
									@endforeach
								@endif
								<div class="thfoot">
									@if ($share_button == 1)
									<script>
										(function(){ //byさとぴあさん
											var url = encodeURIComponent("{{$base}}{{$self}}?mode=res&amp;res={{$bbsline['tid']}}"); //ページURL
											var title = encodeURIComponent("[{{$bbsline['tid']}}] {{$bbsline['sub']}} by {{$bbsline['name']}} - {{$btitle}}"); //ページタイトル
											document.write( '<span class="button"><a target="_blank" href="https://twitter.com/intent/tweet?&amp;text=' + title + '&amp;url=' + url + '"><img src="./templates/{{$themedir}}/icons/twitter.svg" width="16" height="16"> tweet</a></span> <span class="button"><a target="_blank" class="fb btn" href="http://www.facebook.com/share.php?u=' + url + '"><img src="./templates/{{$themedir}}/icons/facebook.svg" width="16" height="16"> share</a></span>' );}
										)();
									</script>
									@endif
									@if ($elapsed_time == 0 || $nowtime - $bbsline['utime'] < $elapsed_time)
										<span class="button"><a href="{{$self}}?mode=res&amp;res={{$bbsline['tid']}}"><img src="./templates/{{$themedir}}/icons/rep.svg" width="16" height="16"> 返信</a></span>
									@else
										このスレは古いので返信できません…
									@endif
									<a href="#header">[↑]</a>
									<hr>
								</div>
							</section>
						</section>
					@endforeach
				@endif
			</div>
			<div>
				<section class="thread">
					<section class="paging">
						<p>
							@if ($back == 0)
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
					<hr>
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
					<form class="delf" action="{{$self}}" method="post">
						<p>
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
							<span class="stylechanger">
								<select class="form" name="select" id="mystyle" onchange="SetCss(this);">
									<option value="mono_red.min.css">Color</option>
									<option value="mono_red.min.css"> RED</option>
									<option value="mono_dev.min.css"> DEV</option>
									<option value="mono_main.min.css"> MONO</option>
									<option value="mono_dark.min.css"> dark</option>
									<option value="mono_deep.min.css"> deep</option>
									<option value="mono_mayo.min.css"> MAYO</option>
								</select>
							</span>
						</p>
					</form>
				</section>
			</div>
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
		<script src="./templates/{{$themedir}}/jquery-3.5.1.min.js"></script>
		<script>
			$(function(){//2度押し対策
				$('[type="submit"]').click(function(){
					$(this).prop('disabled',true);
					$(this).closest('form').submit();
				});
			});
		</script>
	</body>
</html>
