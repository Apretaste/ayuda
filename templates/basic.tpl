<h1>Ayuda de Apretaste</h1>
<p>Apretaste le permite acceder a Internet mediante su email. Con Apretaste usted puede Comprar o Vender, consultar Wikipedia, Traducir documentos a decenas de idiomas, ver el Estado del Tiempo y mucho m&aacute;s; siempre desde su email.</p>

{space15}

<table>
	<tr>
		<td valign="top">
			<h2>Navegue en internet por email</h2>
			<p><b>1.</b> Cree nuevo email. En la secci&oacute;n "Para" escriba: {apretaste_email}</p>
			<p><b>2.</b> En la secci&oacute;n "Asunto" escriba: <span style="color:green;">NAVEGAR</span></p>
			<p><b>3.</b> Env&iacute;e el email. En segundos recibir&aacute; otro email con la p&aacute;gina de inicio del servicio NAVEGAR.</p>
			{space10}
			<center>
				{button href="NAVEGAR revolico.com" caption="Probar NAVEGAR"} {button href="NAVEGAR" caption="Ir a NAVEGAR" color="blue"}

			</center>
		</td>
		<td valign="top">
			{emailbox title="Navegar" from="{$userEmail}" subject="NAVEGAR revolico.com"}
		</td>
	</tr>
</table>


{space30}
{space10}


<table>
	<tr>
		<td valign="top">
			<h2>Busque en la web con Google</h2>
			<p><b>1.</b> Cree un nuevo email. En la secci&oacute;n "Para" escriba: {apretaste_email}</p>
			<p><b>2.</b> En la secci&oacute;n "Asunto" escriba: <span style="color:green;">GOOGLE</span> seguida de una frase referente a lo que desea buscar</p>
			<p><b>3.</b> Env&iacute;e el email. En segundos recibir&aacute; otro email con los mejores resultados de b&uacute;squeda en la web</p>
			{space10}
			<center>
				{button href="GOOGLE pitufos" caption="Probar Google"}
			</center>
		</td>
		<td valign="top">
			{emailbox title="Google" from="{$userEmail}" subject="GOOGLE pitufos"}
		</td>
	</tr>
</table>


{space30}
{space10}


<table>
	<tr>
		<td valign="top">
			<h2>Con&eacute;ctate con miles de Cubanos y deja un trazo de tiza en una gran PIZARRA global</h2>
			<p><b>1.</b> Cree un nuevo email. En la secci&oacute;n "Para" escriba: {apretaste_email}</p>
			<p><b>2.</b> En la secci&oacute;n "Asunto" escriba: <span style="color:green;">PIZARRA</span></p>
			<p><b>4.</b> Env&iacute;e el email. En segundos recibir&aacute; otro email con las &uacute;ltimos cien notas escritas en la pizarra.</p>
			{space10}
			<center>
				{button href="PIZARRA" caption="Probar Traducir" body=""}
			</center>
		</td>
		<td valign="top">
			{emailbox title="Pizarra" from="{$userEmail}" subject="PIZARRA" body=""}
		</td>
	</tr>
</table>


{space30}
{space10}


<table>
	<tr>
		<td valign="top">
			<h2>Busca tu media naranja con CUPIDO</h2>
			<p><b>1.</b> Cree un nuevo email. En la secci&oacute;n "Para" escriba: {apretaste_email}</p>
			<p><b>2.</b> En la secci&oacute;n "Asunto" escriba: <span style="color:green;">CUPIDO</span></p>
			<p><b>3.</b> Env&iacute;e el email. En segundos recibir&aacute; una lista de las personas m&aacute;s afines a usted</p>
			{space10}
			<center>
				{button href="CUPIDO" caption="Probar Cupido"}
			</center>
		</td>
		<td valign="top">
			{emailbox title="Cupido" from="{$userEmail}" subject="CUPIDO"}
		</td>
	</tr>
</table>


{space30}
{space10}


<h1>Tenemos muchos m&aacute;s servicios</h1>
<p>Brindamos muchos m&aacute;s servicios, y todos los meses incrementamos la lista. &#191;Quiere sugerir alg&uacute;n servicio? &#191;Tiene alguna pregunta? Escribanos a <a href="mailto:{apretaste_support_email}">{apretaste_support_email}</a> y le atenderemos al momento.</p>
{space10}
<center>
	{button href="SERVICIOS" caption="Otros servicios"}
</center>

{space30}
{space10}

<h1>Invite a sus amigos y familia</h1>
<p>&#191;Le gusta el trabajo que hacemos? Invite a sus amigos y familia a conocer Apretaste y gane tickets para {link href="RIFA" caption="nuestra rifa mensual"}.</p>
<table>
	<tr>
		<td valign="top">
			<p><b>1.</b> Cree un nuevo email. En la secci&oacute;n "Para" escriba: {apretaste_email}</p>
			<p><b>2.</b> En la secci&oacute;n "Asunto" escriba: <span style="color:green;">INVITAR</span> seguido del email de su amigo</p>
			<p><b>3.</b> Env&iacute;e el email. Su amigo ser&aacute; invitado y usted recibir&aacute; tickets para {link href="RIFA" caption="nuestra rifa"}</p>
			{space10}
			<center>
				{button href="INVITAR su@amigo.cu" caption="Invitar un amigo"}
			</center>
		</td>
		<td valign="top">
			{emailbox title="Invitar a un amigo" from="{$userEmail}" subject="INVITAR su@amigo.cu"}
		</td>
	</tr>
</table>
{space10}
