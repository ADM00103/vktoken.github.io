<?php
/**
 * @package        VK API - Get server token
 * @version        1.0
 * @author         Igor Berdicheskiy - septdir.ru
 * @copyright      Copyright (c) 2013 - 2018 Igor Berdicheskiy. All rights reserved.
 * @license        GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

ini_set('display_errors', 1);
ini_set('error_reporting', 2047);

$client_id = '';
if (!empty($_COOKIE['client_id']))
{
	$client_id = $_COOKIE['client_id'];
}
if (!empty($_POST['client_id']))
{
	$client_id = $_POST['client_id'];
}
$client_secret = '';
if (!empty($_COOKIE['client_secret']))
{
	$client_secret = $_COOKIE['client_secret'];
}
if (!empty($_POST['client_secret']))
{
	$client_secret = $_POST['client_secret'];
}
$user_id = '';
if (!empty($_COOKIE['user_id']))
{
	$user_id = $_COOKIE['user_id'];
}
if (!empty($_POST['user_id']))
{
	$user_id = $_POST['user_id'];
}
$code = '';
if (!empty($_POST['code']))
{
	$code = $_POST['code'];
}
$scope      = '';
$scopeArray = array();
if (!empty($_COOKIE['scope']))
{
	$scope      = $_COOKIE['scope'];
	$scopeArray = explode(',', $scope);
}
if (!empty($_POST['scope']))
{
	$scopeArray = $_POST['scope'];
	$scope      = implode(',', $scopeArray);
}
$redirect_uri = 'https://oauth.vk.com/blank.html';
$display      = 'page';
$done         = false;
$response     = new stdClass();
if (!empty($client_id) && !empty($client_secret) && !empty($user_id) && !empty($scopeArray))
{
	setcookie('client_id', $client_id, time() + 3600);
	setcookie('client_secret', $client_secret, time() + 3600);
	setcookie('user_id', $user_id, time() + 3600);
	setcookie('scope', $scope, time() + 3600);
	$done                    = true;
	$params                  = array();
	$params['client_id']     = $client_id;
	$params['client_secret'] = $client_secret;
	$params['user_id']       = $user_id;
	$params['scope']         = $scope;
	if (!empty($code))
	{
		$params['code'] = $code;
	}
	$params['redirect_uri'] = $redirect_uri;
	$params['display']      = $display;
}
if (isset($_POST['sumitform']) && $_POST['sumitform'] == '1' && !$done)
{
	$response->error             = 'error';
	$response->error_description = 'form_error';
}

if (isset($_POST['sumitform']) && $_POST['sumitform'] == '1' && $done)
{
	if (empty($code))
	{
		$url = 'https://oauth.vk.com/authorize?';
		$url .= http_build_query($params);
		header("Location: $url");
	}
	$url                         = 'https://oauth.vk.com/access_token?';
	$url                         .= http_build_query($params);
	$response->error             = 'error';
	$response->error_description = 'curl_error';
	if (function_exists('curl_init'))
	{
		unset($response->error);
		unset($response->error_description);
		$param = parse_url($url);
		if ($curl = curl_init())
		{
			curl_setopt($curl, CURLOPT_URL, $param['scheme'] . '://' . $param['host'] . $param['path']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $param['query']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response);
		}

	}
}
if (!empty($response->access_token))
{
	setcookie('client_id', '', time() + 1);
	setcookie('client_secret', '', time() + 1);
	setcookie('user_id', '', time() + 1);
	setcookie('scope', '', time() + 1);
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Get vk token / Получение токена vk</title>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.2/css/uikit.almost-flat.min.css" rel="stylesheet"
		  type="text/css"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.2/js/uikit.min.js" type="text/javascript"></script>
	<script>
		$(document).ready(function () {
			console.log('666');
			$('#getToken').submit(function () {
				var formData = $(this).serializeArray();
				var errors = [];
				$(formData).each(function (x) {
					if (formData[x]["value"] == "") {
						if (formData[x]["name"] !== "code") {
							errors.push(formData[x]);
						}
					}
				});
				if (errors.length > 0) {
					$(errors).each(function (x) {
						$('#getToken').find('[name="' + errors[x].name + '"]').addClass('uk-form-danger');
					});
					console.log(errors);
					return false;
				}
				if ($('[name="code"]').val() == '') {
					$(this).attr('target', '_blank');
				}
				else {
					$(this).removeAttr('target');
				}
			});
		});
	</script>
</head>
<body>
<div class="uk-container uk-container-center uk-margin-top">
	<h1 class="uk-h2">
		Get vk.com token / Получение токена vk.com
	</h1>
	<?php if (!empty($response->access_token)): ?>
		<div class="uk-alert uk-alert-success" data-uk-alert>
			<a href="" class="uk-alert-close uk-close">
			</a>
			<dl class="uk-description-list-horizontal">
				<dt>
					access_token
				</dt>
				<dd>
					<?php echo $response->access_token; ?>
				</dd>
			</dl>
			<dl class="uk-description-list-horizontal">
				<dt>
					expires_in
				</dt>
				<dd>
					<?php echo $response->expires_in; ?>
				</dd>
			</dl>
			<dl class="uk-description-list-horizontal">
				<dt>
					user_id
				</dt>
				<dd>
					<?php echo $response->user_id; ?>
				</dd>
			</dl>
		</div>
	<?php endif; ?>
	<?php if (!empty($response->error)): ?>
		<div class="uk-alert uk-alert-danger" data-uk-alert>
			<a href="" class="uk-alert-close uk-close">
			</a>
			<p>
				<?php echo $response->error_description; ?>
			</p>
		</div>
	<?php endif; ?>
	<form id="getToken" method="post" class="uk-form uk-form-horizontal">
		<div class="uk-form-row">
			<label class="uk-form-label">
				Client ID / ID приложения<span class="uk-text-danger">*</span>
			</label>
			<div class="uk-form-controls">
				<input type="text" value="<?php echo $client_id; ?>" name="client_id" class="uk-form-width-large"/>
			</div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label">
				Client Secret / Защищенный ключ<span class="uk-text-danger">*</span>
			</label>
			<div class="uk-form-controls">
				<input type="text" value="<?php echo $client_secret; ?>" name="client_secret"
					   class="uk-form-width-large"/>
			</div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label">
				User ID / ID Пользователя<span class="uk-text-danger">*</span>
			</label>
			<div class="uk-form-controls">
				<input type="text" value="<?php echo $user_id; ?>" name="user_id" class="uk-form-width-large"/>
			</div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label">
				Code / Код
			</label>
			<div class="uk-form-controls">
				<input type="text" value="<?php echo $code; ?>" name="code" class="uk-form-width-large"/>
			</div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label">
				Scope / Права доступа
			</label>
			<div class="uk-form-controls">
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="notify" <?php if (in_array('notify', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>notify</strong> - Пользователь разрешил отправлять ему уведомления (для
						flash/iframe-приложений).
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="friends" <?php if (in_array('friends', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>friends</strong> - Доступ к друзьям.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="photos" <?php if (in_array('photos', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>photos</strong> - Доступ к фотографиям.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="audio" <?php if (in_array('audio', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>audio</strong> - Доступ к аудиозаписям.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="video" <?php if (in_array('video', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>video</strong> - Доступ к видеозаписям.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="pages" <?php if (in_array('pages', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>pages</strong> - Доступ к wiki-страницам.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="status" <?php if (in_array('status', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>status</strong> - Доступ к статусу пользователя.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="notes" <?php if (in_array('notes', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>notes</strong> - Доступ к заметкам пользователя.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="messages" <?php if (in_array('messages', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>messages</strong> - Доступ к расширенным методам работы с сообщениями (только для
						Standalone-приложений).
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="wall" <?php if (in_array('wall', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>wall</strong> - Доступ к обычным и расширенным методам работы со стеной.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="ads" <?php if (in_array('ads', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>ads</strong> - Доступ к расширенным методам работы с рекламным API.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="offline" <?php if (in_array('offline', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<span class="uk-text-success uk-text-large"><strong>offline</strong>  - Доступ к API в любое время.</span>
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="docs" <?php if (in_array('docs', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>docs</strong> - Доступ к документам.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="groups" <?php if (in_array('groups', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>groups</strong> - Доступ к группам пользователя.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="notifications" <?php if (in_array('notifications', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>notifications</strong> - Доступ к оповещениям об ответах пользователю.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="stats" <?php if (in_array('stats', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>stats</strong> - Доступ к статистике групп и приложений пользователя, администратором
						которых он является.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="email" <?php if (in_array('email', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>email</strong> - Доступ к email пользователя.
					</label>
				</p>
				<p class="uk-form-controls-condensed">
					<label>
						<input type="checkbox" value="market" <?php if (in_array('market', $scopeArray))
						{
							echo 'checked';
						} ?> name="scope[]"/>
						<strong>market</strong> - Доступ к товарам.
					</label>
			</div>
		</div>
		<div class="uk-form-row uk-text-center">
			<button type="submit" class="uk-button uk-button-success uk-button-large" name="sumitform" value="1">
				Send / Отправить
			</button>
	</form>
</div>
</body>
</html>