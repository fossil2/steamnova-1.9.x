{block name="title" prepend}{$LNG.tut_welcome}{/block}
{block name="content"}
<div class="container container-page" style="width: 100%;">
	<div class="title text-center">
		{$LNG.tut_tut}
		<dl> 
		</dl>
	</div>
		<tr>	<th scope="col"><a href="game.php?page=tutorial&mode=m1">{$LNG.tut_m1} {$Si1}{$No1}</a></th>
				<th scope="col"><a href="game.php?page=tutorial&mode=m2">{$LNG.tut_m2} {$Si2}{$No2}</a></th> 
				<th scope="col"><a href="game.php?page=tutorial&mode=m3">{$LNG.tut_m3} {$Si3}{$No3}</a></th></tr>                           				<tr>	<th scope="col"><a href="game.php?page=tutorial&mode=m4">{$LNG.tut_m4} {$Si4}{$No4}</a></th>
			    <th scope="col"><a href="game.php?page=tutorial&mode=m5">{$LNG.tut_m5} {$Si5}{$No5}</a></th>
				<th scope="col"><a href="game.php?page=tutorial&mode=m6">{$LNG.tut_m6} {$Si6}{$No6}</a></th></tr>
		<tr>	<th scope="col"><a href="game.php?page=tutorial&mode=m7">{$LNG.tut_m7} {$Si7}{$No7}</a></th>
				<th scope="col"><a href="game.php?page=tutorial&mode=m8">{$LNG.tut_m8} {$Si8}{$No8}</a></th>
				<th scope="col"><a href="game.php?page=tutorial&mode=m9">{$LNG.tut_m9} {$Si9}{$No9}</a></th>
		    	</tr>
            	<dl> 
            	</dl>
	            <tr>
				<td colspan="9">
				<h5 class="textBeefy text-center k">{$LNG.tut_m2_name} - {$livello2} {$Si2}{$No2}</h5>
				</td>
		        <div align='center'>
				<a href ="game.php?page=buildings"><img src="{$dpath}gebaeude/3.gif"></a>
				<p>{$LNG.tut_m2_desc}</p>
				</div>
				<div align='center'>
					<h3>{$LNG.tut_objects}:</h3>

						<ul id="aufgabe_liste">
							<li class="aufzaehlungszeichen">{$LNG.tut_m2_quest} {$Si_m2_1}{$No_m2_1}{$Si2}</li>
							<li class="aufzaehlungszeichen">{$LNG.tut_m2_quest2} {$Si_m2_2}{$No_m2_2}{$Si2}</li>
							<li class="aufzaehlungszeichen">{$LNG.tut_m2_quest3} {$Si_m2_3}{$No_m2_3}{$Si2}</li>
							<li class="aufzaehlungszeichen">{$LNG.tut_m2_quest4} {$Si_m2_4}{$No_m2_4}{$Si2}</li>
						</ul>
         			     <div style="color:orange;">{$LNG.tut_m2_gain}</div>
					</div>
				</tr>
		    	{if $Si2}
		    	<tr>
				<td>
					<a href ="game.php?page=tutorial&mode=m3"><input type="submit" class="btn btn-sm btn-dark" value="{$LNG.tut_go_to} {$LNG.tut_m3}" onclick="window.location = 'game.php?page=tutorial&mode=m3'"/></a>
				</td>
			</tr>
			{/if}
		</thead>
	</table>
</div>
{/block}