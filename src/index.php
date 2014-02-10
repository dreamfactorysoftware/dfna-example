<?php
/**
 * This file is part of the DreamFactory DFNA Application
 * Copyright 2013 DreamFactory Software, Inc. {@email support@dreamfactory.com}
 *
 * DreamFactory DFNA Application {@link http://github.com/dreamfactorysoftware/dfna-example}
 * DreamFactory Oasys(tm) {@link http://github.com/dreamfactorysoftware/oasys}
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
use DreamFactory\Platform\Exceptions\InternalServerErrorException;
use DreamFactory\Platform\Utility\Platform;
use DreamFactory\Platform\Yii\Models\App;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Curl;

//*************************************************************************
//	Constants
//*************************************************************************

/**
 * @var string
 */
const APPLICATION_NAME = 'dfna-example';

//********************************************************************************
//* Bootstrap and Debugging
//********************************************************************************

$_path = ( is_link( __DIR__ ) ? readlink( __DIR__ ) : __DIR__ );
/** @noinspection PhpIncludeInspection */
require_once $_path . '/../vendor/autoload.php';

//	Debugging?
if ( \Kisma::getDebug() )
{
	error_reporting( -1 );
	ini_set( 'display_errors', 1 );
}

//	Must be logged in...
if ( Pii::guest() )
{
	header( 'Location: /' );
	die();
}

//*************************************************************************
//	Grab the apps for the dropdown
//*************************************************************************

$_apps = null;
$_models = ResourceStore::model( 'app' )->findAll(
	array(
		'select' => 'id, api_name, name',
		'order'  => 'name'
	)
);

if ( !empty( $_models ) )
{
	/** @var App[] $_models */
	foreach ( $_models as $_model )
	{
		$_attributes = array( 'value' => $_model->api_name, 'name' => $_model->api_name );

		if ( APPLICATION_NAME == $_model->api_name )
		{
			$_attributes['selected'] = 'selected';
		}

		$_apps .= HtmlMarkup::tag( 'option', $_attributes, $_model->name );
		unset( $_model );
	}

	unset( $_models );
}

$_defaultUrl = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/views/_head.php'; ?>
<body>
<div id="wrap">
	<?php require_once __DIR__ . '/views/_navbar.php'; ?>

	<div class="container">
		<h1>DFNA Example</h1>
		<blockquote>
			<p>An example application using the DSP's Remote Web Service to access the local NetApp WFA's REST API. Credentials are stored in the service definition and never seen by the client.</p>

			<p>The WFA REST API only returns data in XML format. This is translated to JSON by the DSP upon return.</p>
		</blockquote>
		<section id="call-settings">
			<div class="panel-group" id="call-settings-group">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#call-settings-group" href="#session-form-body">Call Settings</a>
						</h4>
					</div>
					<div id="session-form-body" class="panel-collapse collapse in">
						<div class="panel-body">
							<form class="form-horizontal" id="call-settings-form">
								<div class="form-group">
									<label for="request-uri" class="col-sm-2 control-label">Resource</label>

									<div class="col-sm-10">
										<div class="input-group">
											<span id="request-server" class="input-group-addon muted">https://*.cloud.dreamfactory.com/rest/wfa/</span>

											<input type="text"
												   class="form-control"
												   id="request-uri"
												   value="<?php echo $_defaultUrl; ?>"
												   placeholder="The request URI (i.e. /user)">
										</div>
									</div>
								</div>
								<div class="form-group row">
									<label for="request-method" class="col-sm-2 control-label">Method</label>

									<div class="col-sm-4">
										<select class="form-control" id="request-method">
											<option value="GET">GET</option>
											<option value="POST">POST</option>
											<option value="PUT">PUT</option>
											<option value="PATCH">PATCH</option>
											<option value="MERGE">MERGE</option>
											<option value="DELETE">DELETE</option>
											<option value="OPTIONS">OPTIONS</option>
											<option value="COPY">COPY</option>
										</select>
									</div>
									<label for="request-app" class="col-sm-2 control-label">App/API Key</label>

									<div class="col-sm-4">
										<select class="form-control" id="request-app">
											<optgroup label="Built-In">
												<option value="admin">admin</option>
												<option value="launchpad">launchpad</option>
											</optgroup>
											<optgroup label="Available">
												<?php echo $_apps; ?>
											</optgroup>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label for="request-body" class="col-sm-2 control-label">Body</label>

									<div class="col-sm-10">
										<textarea id="request-body" rows="2" class="form-control"></textarea>

										<p class="help-block">Must be valid JSON</p>
									</div>
								</div>
								<hr />
								<div class="form-group">
									<div class="form-buttons">
										<button id="reset-request" type="button" class="btn btn-danger">Reset</button>
										<button id="send-request" type="button" class="btn btn-warning">Send Request</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section id="dfna-results">
			<div class="panel-group" id="call-results-group">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a name="dfna-results" data-toggle="collapse" data-parent="#call-results-group" href="#call-results-body">Call Results </a>
							<span id="request-elapsed"></span> <span id="loading-indicator" class="pull-right"><i class="fa fa-spinner"></i></span>
						</h4>
					</div>
					<div id="call-results-body" class="panel-collapse collapse in">
						<div class="panel-body">
							<div id="example-code">
								<small>Ready</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

<?php require_once( 'views/_footer.php' ); ?>

<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
<script src="//google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
<script src="js/app.jquery.js"></script>
<script>
//	This needs to be last because _options is defined in app.jquery.js... lame, I know...
_options.baseUrl = '<?php echo Curl::currentUrl( false, false ); ?>';
_options.APPLICATION_NAME = '<?php echo APPLICATION_NAME; ?>';
</script>
</body>
</html>
