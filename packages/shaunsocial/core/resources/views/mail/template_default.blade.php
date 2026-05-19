<div style="background-color: #ddd; padding: 30px; color: #fff">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 650px; margin: 0 auto">
		<tbody>
			<tr>
				<td><img src="{{setting('site.logo')}}" style="max-width: 160px"/></td>
			</tr>
			<tr height="30">
				<td style="padding:0 0 0 0;height:30px"></td>
			</tr>
			<tr bgcolor="#FFFFFF">
				<td style="font-size: 16px; color: #4F4F4F; padding: 50px; border-radius: 10px">
                    {!! $contentTemplate !!}			
				</td>
			</tr>
			<tr height="30">
				<td style="padding:0 0 0 0;height:30px"></td>
			</tr>
			<tr>
				<td>
					<p style="margin-top: 0; font-size: 12px; line-height: 15px; color: #000">{!! __("If you don't want to receive these emails in the future, please click :link to unsubscribe",['link' =>'<a href="'.$unsubscribeLink.'" style="font-weight:bold">'.__('here').'</a>']) !!}</p>
				</td>
			</tr>
		</tbody>
	</table>
</div>