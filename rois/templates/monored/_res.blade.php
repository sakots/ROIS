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
		@foreach ($oya as $bbsline)
		<meta name="twitter:card" content="summary">
		<meta property="og:title" content="[{{$bbsline['tid']}}] {{$bbsline['sub']}} by {{$bbsline['name']}} - {{$btitle}}">
		<meta property="og:type" content="article">
		<meta property="og:url" content="{{$base}}{{$self}}?mode=res&amp;res={{$resno}}">
		@if (isset($bbsline['picfile']))<meta property="og:image" content="{{$base}}{{$path}}{{$bbsline['picfile']}}"> @endif
		<meta property="og:site_name"  content="">
		<meta property="og:description" content="{{$bbsline['com']}}">
		@endforeach
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
						<a href="{{$self}}">[トップ]</a>
						<a href="#footer">[↓]</a>
					</p>
				</section>
				<section>
					<hr>
					<p>RES MODE</p> 
					<p class="sysmsg">{{$message}}</p>
				</section>
			</div>
			<hr>
		</header>
		<main>
			<div class="thread">
				@foreach ($oya as $bbsline)
					@if (isset($bbsline['com']))
						<section>
							<h3 class="oyat">
								<span class="oyano">[{{$bbsline['tid']}}]</span>
								{{$bbsline['sub']}}
							</h3>
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
										<span class="id">ID : {{$bbsline['id']}}</span>
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
											<figcaption>
												<a href="{{$path}}{{$bbsline['picfile']}}" target="_blank" data-title="{{$bbsline['picfile']}}">{{$bbsline['picfile']}}</a>
												@if ($bbsline['pchfile'] != null)
													<a href="{{$self}}?mode=anime&amp;pch={{$bbsline['pchfile']}}">●動画</a>
												@endif
												@if ($use_continue == 1)
													<a href="{{$self}}?mode=continue&amp;no={{$bbsline['picfile']}}">●続きを描く</a>
												@endif
											</figcaption>
											<a href="{{$path}}{{$bbsline['picfile']}}" target="_blank"  data-lightbox="{{$bbsline['picfile']}}" data-title="{{$bbsline['picfile']}}"><img src="{{$path}}{{$bbsline['picfile']}}" alt="{{$bbsline['picfile']}}"></a>
										</figure>
									@endif
								<p class="comment oya">{!!$bbsline['com']!!}</p>
								@if (($m_tid - $bbsline['tid']) > $thid)
									<div class="res">
										<p class="limit">このスレは古いのでもうすぐ消えます。</p>
									</div>
								@endif
								@if (!empty($ko))
									@foreach ($ko as $res)
										@if ($res['com'] == true)
											<section class="res">
												<section>
													<h3>
														<span class="oyano">[{{$res['iid']}}]</span>
														{{$res['sub']}}
													</h3>
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
														<span class="sodane"><a href="{{$self}}?mode=rsodane&amp;resto={{$res['iid']}}">そうだね@if ($res['exid'] == 0)
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
									@endforeach
								@else
								@endif
							</section>
						<hr>	
						</section>
						@if ($share_button == 1)
							<div class="thfoot">
								<script>
									(function(){ //byさとぴあさん
										var url = encodeURIComponent("{{$base}}{{$self}}?mode=res&amp;res={{$resno}}"); //ページURL
										var title = encodeURIComponent("[{{$bbsline['tid']}}] {{$bbsline['sub']}} by {{$bbsline['name']}} - {{$btitle}}"); //ページタイトル
										document.write( '<span class="button"><a target="_blank" href="https://twitter.com/intent/tweet?&amp;text=' + title + '&amp;url=' + url + '"><img src="./templates/{{$themedir}}/icons/twitter.svg" width="16" height="16"> tweet</a></span> <span class="button"><a target="_blank" class="fb btn" href="http://www.facebook.com/share.php?u=' + url + '"><img src="./templates/{{$themedir}}/icons/facebook.svg" width="16" height="16"> share</a></span>' );}
									)();
								</script>
							</div>
						@endif
					@endif
				<div>
					@foreach ($oya as $bbsline)
						@if (!empty($bbsline['com']))
							<section>
								<h3 class="oekaki">このスレにレス</h3>
								<script>function add_to_com(){
									document.getElementById("p_input_com").value += "{{$resname}}さん";
								}
								</script>
								@if ($elapsed_time == 0 || $nowtime - $bbsline['utime'] < $elapsed_time)
									<p>
										<button class="copy_button" onclick="add_to_com()">投稿者名をコピー</button>
										（投稿者名をコピぺできます）
									</p>
									<form action="{{$self}}?mode=regist" method="post" class="postform">
										<table>
											<tr>
												<td>
													name
													@if ($use_name == 1)
														*
													@endif
												</td>
												<td><input type="text" name="name" size="18" value="" autocomplete="username"></td>
											</tr>
											<tr>
												<td>mail</td>
												<td><input type="text" name="mail" size="18" value="" autocomplete="email"></td>
											</tr>
											<tr>
												<td>URL</td>
												<td><input type="text" name="url" size="18" value="" autocomplete="url"></td>
											</tr>
											<tr>
												<td>
													subject
													@if ($use_sub == 1)
														*
													@endif
												</td>
												<td>
													@if ($use_resub == 1)
														<input type="text" name="sub" size="18" value="Re:{{$bbsline['sub']}}" autocomplete="section-sub">
													@else
														<input type="text" name="sub" size="18" value="" autocomplete="section-sub">
													@endif
													<input type="submit" name="send" value="書き込む">
													<input type="hidden" name="picfile" value="">
													<input type="hidden" name="parent" value="{{$resno}}">
													<input type="hidden" name="invz" value="0">
													<input type="hidden" name="img_w" value="0">
													<input type="hidden" name="img_h" value="0">
													<input type="hidden" name="time" value="0">
													<input type="hidden" name="exid" value="0">
													<input type="hidden" name="modid" value="{{$resno}}">
												</td>
											</tr>
											<tr>
												<td>
													comment
													@if ($use_com == 1)
														*
													@endif
												</td>
												<td>
													<textarea name="com" rows="4" cols="48" id="p_input_com"></textarea>
												</td>
											</tr>
											<tr>
												<td>pass</td>
												<td>
													<input type="password" name="pwd" size="8" value="" autocomplete="current-password">
													(記事の編集削除用。英数字で)
												</td>
											</tr>
										</table>
									</form>
								@else
									<p>このスレは古いので返信できません</p>
								@endif
							</section>
						@endif
					@endforeach
				</div>
				<div class="thfoot">
					<a href="#header">[↑]</a>
				</div>
			@endforeach
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
					<a href="https://github.com/EFTEC/BladeOne" target="_top" rel="noopener noreferrer" title="by EFTEC">BladeOne</a>
				</p>
			</div>
		</footer>
	</body>
</html>
