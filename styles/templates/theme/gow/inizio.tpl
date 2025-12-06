{block name="title" prepend}{$LNG.tut_welcome}{/block}
{block name="content"}
<div class="container container-page" style="width: 100%;">
	<div align='center'>
	<div class="title k">
		{$tut_welcome}
	</div>
	<div class="container k">
		<div class="alert alert-dark text-center" role="alert">
		  	<h5>{$LNG.tut_welcom_desc}</h5>
			  	<ul id="aufgabe_liste">
			  		<li class="aufzaehlungszeichen"><h6>{$LNG.tut_welcom_desc2}</h6></li>
			  		<li class="aufzaehlungszeichen"><h6>{$LNG.tut_welcom_desc3}</h6></li>
			  		<li class="aufzaehlungszeichen"><h6>{$LNG.tut_welcom_desc4}</h6></li>
			  		<li class="aufzaehlungszeichen"><h6>{$LNG.tut_welcom_desc5}</h6></li>
			  	</ul>
		</div>
		<div class="text-center">
			<a href="?page=tutorial&mode=m1" ><input type="submit" class="btn btn-sm btn-dark" value="{$LNG.tut_go}" onclick="window.location = '?page=tutorial&mode=m1'" /></input></a>
		</div>
	</div>
</div>
		
</thead>
</div>
{/block}